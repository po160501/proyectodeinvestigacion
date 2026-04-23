<?php

namespace App\Http\Controllers;

use App\Models\MedicionRuido;
use App\Models\Sensor;
use App\Models\Alerta;
use App\Models\Obra;
use App\Models\ExposicionRuido;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoreoController extends Controller
{
    public function index()
    {
        return view('monitoreo.index');
    }

    // Endpoint AJAX — retorna obras, trabajadores y ponderado
    public function datos(Request $request)
    {
        $hoy          = Carbon::today();
        $obraId       = $request->query('obra_id');
        $trabajadorId = $request->query('trabajador_id');

        // ── Obras con ponderado ──
        $obras = Obra::with([
            'areas',
            'areas.trabajadores' => fn($q) => $q->where('nombre', '!=', 'Pendiente'),
            'areas.trabajadores.exposiciones' => fn($q) => $q->whereDate('fecha', $hoy)->latest(),
        ])->get()->map(function ($obra) {
            $allTrab = $obra->areas->flatMap->trabajadores;
            $dbs = $allTrab->map(fn($t) => $t->exposiciones->first()?->decibeles ?? null)->filter()->values();

            return [
                'id'        => $obra->id,
                'nombre'    => $obra->nombre,
                'limite_db' => $obra->limite_db,
                'ponderado' => $dbs->count() ? round($dbs->avg(), 1) : null,
                'total'     => $allTrab->count(),
                'areas'     => $obra->areas->map(function ($a) use ($obra) {
                    $dbs = $a->trabajadores->map(fn($t) => $t->exposiciones->first()?->decibeles ?? null)->filter()->values();
                    return [
                        'id'        => $a->id,
                        'nombre'    => $a->nombre,
                        'ponderado' => $dbs->count() ? round($dbs->avg(), 1) : null,
                        'total'     => $a->trabajadores->count(),
                    ];
                })->values(),
            ];
        });

        // ── Trabajadores de la obra seleccionada ──
        $trabajadores = [];
        if ($obraId) {
            $trabajadores = \App\Models\Trabajador::where('obra_id', $obraId)
                ->where('nombre', '!=', 'Pendiente')
                ->with(['exposiciones' => fn($q) => $q->whereDate('fecha', $hoy)->orderByDesc('created_at')])
                ->get()
                ->map(fn($t) => [
                    'id'      => $t->id,
                    'nombre'  => $t->nombre,
                    'area_id' => $t->area_id,
                    'db'      => $t->exposiciones->first()?->decibeles ?? null,
                    'hora'    => $t->exposiciones->first()?->hora_fin ?? null,
                ])->values();
        }

        // ── Historial: trabajador individual ──
        $historial = [];
        $titulo    = null;
        if ($trabajadorId) {
            $t = \App\Models\Trabajador::find($trabajadorId);
            $titulo = $t?->nombre;
            $historial = ExposicionRuido::where('trabajador_id', $trabajadorId)
                ->whereDate('fecha', $hoy)
                ->orderBy('hora_fin')
                ->get()
                ->map(fn($e) => ['hora' => substr($e->hora_fin, 0, 5), 'db' => (float)$e->decibeles])
                ->values();
        } elseif ($obraId) {
            // Historial ponderado de la obra
            $obra   = Obra::find($obraId);
            $titulo = $obra?->nombre;
            $ids    = \App\Models\Trabajador::where('obra_id', $obraId)->pluck('id');
            $historial = ExposicionRuido::whereIn('trabajador_id', $ids)
                ->whereDate('fecha', $hoy)
                ->select(DB::raw('LEFT(hora_fin,5) as hora'), DB::raw('AVG(decibeles) as db'))
                ->groupBy(DB::raw('LEFT(hora_fin,5)'))
                ->orderBy('hora')
                ->get()
                ->map(fn($r) => ['hora' => $r->hora, 'db' => round($r->db, 1)])
                ->values();
        }

        // Límite según contexto
        $limite = 85;
        if ($obraId) $limite = Obra::find($obraId)?->limite_db ?? 85;

        return response()->json(compact('obras', 'trabajadores', 'historial', 'titulo', 'limite'));
    }

    // Endpoint para recibir datos desde ESP32/Arduino
    public function recibirDato(Request $request)
    {
        $request->validate([
            'sensor_id' => 'required|exists:sensores,id',
            'decibeles' => 'required|numeric|min:0|max:200',
        ]);

        $now = Carbon::now();

        $medicion = MedicionRuido::create([
            'sensor_id' => $request->sensor_id,
            'decibeles' => $request->decibeles,
            'fecha' => $now->toDateString(),
            'hora' => $now->toTimeString(),
        ]);

        // Actualizar nivel actual del sensor
        Sensor::where('id', $request->sensor_id)->update(['nivel_actual' => $request->decibeles]);

        // Generar alerta automática si supera 85 dB
        if ($request->decibeles >= 85) {
            Alerta::create([
                'sensor_id' => $request->sensor_id,
                'nivel_ruido' => $request->decibeles,
                'fecha' => $now->toDateString(),
                'hora' => $now->toTimeString(),
                'estado' => 'activa',
            ]);
        }

        return response()->json(['ok' => true, 'id' => $medicion->id]);
    }
}
