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
        $hoy     = Carbon::today();
        $obraId  = $request->query('obra_id');

        // Obras con ponderado de dB (promedio de última medición de cada trabajador)
        $obras = Obra::with(['trabajadores' => function ($q) use ($hoy) {
            $q->with(['exposiciones' => function ($e) use ($hoy) {
                $e->whereDate('fecha', $hoy)->latest()->limit(1);
            }]);
        }])->get()->map(function ($obra) {
            $dbs = $obra->trabajadores->map(function ($t) {
                return optional($t->exposiciones->first())->decibeles ?? null;
            })->filter()->values();

            return [
                'id'        => $obra->id,
                'nombre'    => $obra->nombre,
                'limite_db' => $obra->limite_db,
                'ponderado' => $dbs->count() ? round($dbs->avg(), 1) : null,
                'total'     => $obra->trabajadores->count(),
            ];
        });

        // Trabajadores de la obra seleccionada con su último dB
        $trabajadores = [];
        if ($obraId) {
            $trabajadores = \App\Models\Trabajador::where('obra_id', $obraId)
                ->where('nombre', '!=', 'Pendiente')
                ->with(['exposiciones' => function ($q) use ($hoy) {
                    $q->whereDate('fecha', $hoy)->orderByDesc('created_at');
                }])
                ->get()
                ->map(function ($t) {
                    $exp = $t->exposiciones;
                    return [
                        'id'     => $t->id,
                        'nombre' => $t->nombre,
                        'db'     => $exp->first()?->decibeles ?? null,
                        'hora'   => $exp->first()?->hora_fin ?? null,
                        'historial' => $exp->take(20)->map(fn($e) => [
                            'hora' => substr($e->hora_fin, 0, 5),
                            'db'   => (float) $e->decibeles,
                        ])->values(),
                    ];
                });
        }

        // Historial ponderado de la obra (promedio de todos los trabajadores por hora)
        $historialObra = [];
        if ($obraId) {
            $ids = \App\Models\Trabajador::where('obra_id', $obraId)->pluck('id');
            $historialObra = ExposicionRuido::whereIn('trabajador_id', $ids)
                ->whereDate('fecha', $hoy)
                ->select(DB::raw('LEFT(hora_fin,5) as hora'), DB::raw('AVG(decibeles) as db'))
                ->groupBy(DB::raw('LEFT(hora_fin,5)'))
                ->orderBy('hora')
                ->get()
                ->map(fn($r) => ['hora' => $r->hora, 'db' => round($r->db, 1)])
                ->values();
        }

        // Sensores (mantener compatibilidad)
        $sensores = Sensor::where('estado', 'activo')->get()->map(function ($s) {
            $ultima = MedicionRuido::where('sensor_id', $s->id)->latest()->first();
            return [
                'id'        => $s->id,
                'nombre'    => $s->nombre,
                'ubicacion' => $s->ubicacion,
                'decibeles' => $ultima ? (float) $ultima->decibeles : 0,
                'critico'   => $ultima && $ultima->decibeles >= 85,
            ];
        });

        return response()->json(compact('obras', 'trabajadores', 'historialObra', 'sensores'));
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
