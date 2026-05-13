<?php

namespace App\Http\Controllers;

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
        $hoy = Carbon::today();
        $obraId = $request->query('obra_id');
        $areaId = $request->query('area_id');
        $trabajadorId = $request->query('trabajador_id');

        // ── Obras con ponderado ──
        $obras = Obra::with([
            'areas',
            'areas.trabajadores' => fn($q) => $q->where('nombre', '!=', 'Pendiente'),
            'areas.trabajadores.exposiciones' => fn($q) => $q->where('updated_at', '>=', Carbon::now()->subMinute())->latest(),
        ])->get()->map(function ($obra) {
            $areasData = $obra->areas->map(function ($a) {
                $dbs = $a->trabajadores->map(fn($t) => $t->exposiciones->first()?->decibeles ?? null)
                    ->filter(fn($val) => $val !== null && $val > 0)
                    ->values();
                
                return [
                    'id' => $a->id,
                    'nombre' => $a->nombre,
                    'ponderado' => $dbs->count() ? round($dbs->avg(), 1) : null,
                    'total' => $a->trabajadores->count(),
                ];
            })->values();

            // Solo promediamos las áreas que tienen trabajadores activos (ponderado no nulo)
            $activeAreasDbs = $areasData->pluck('ponderado')->filter();

            return [
                'id' => $obra->id,
                'nombre' => $obra->nombre,
                'limite_db' => $obra->limite_db,
                'ponderado' => $activeAreasDbs->count() ? round($activeAreasDbs->avg(), 1) : null,
                'total' => $obra->areas->flatMap->trabajadores->count(),
                'areas' => $areasData,
            ];
        });

        // ── Trabajadores de la obra seleccionada ──
        $trabajadores = [];
        if ($obraId) {
            $trabajadores = \App\Models\Trabajador::where('obra_id', $obraId)
                ->where('nombre', '!=', 'Pendiente')
                ->with([
                    'exposiciones' => fn($q) => $q->where('updated_at', '>=', Carbon::now()->subMinute())->orderByDesc('created_at'),
                    'areaRel'
                ])
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'nombre' => $t->area_id && $t->areaRel ? $t->areaRel->nombre : $t->nombre,
                    'area_id' => $t->area_id,
                    'db' => $t->exposiciones->first()?->decibeles ?? null,
                    'hora' => $t->exposiciones->first()?->hora_fin ?? null,
                ])->values();
        }

        // ── Historial: trabajador individual ──
        $historial = [];
        $titulo = null;
        if ($trabajadorId) {
            $t = \App\Models\Trabajador::find($trabajadorId);
            $titulo = $t?->nombre;
            $historial = ExposicionRuido::where('trabajador_id', $trabajadorId)
                ->whereDate('fecha', $hoy)
                ->orderBy('hora_fin')
                ->get()
                ->map(fn($e) => ['hora' => substr($e->hora_fin, 0, 5), 'db' => (float) $e->decibeles])
                ->values();
        } elseif ($areaId) {
            // Historial ponderado del área
            $area = \App\Models\Area::find($areaId);
            $titulo = $area?->nombre;
            $ids = \App\Models\Trabajador::where('area_id', $areaId)->pluck('id');
            $historial = ExposicionRuido::whereIn('trabajador_id', $ids)
                ->whereDate('fecha', $hoy)
                ->select(DB::raw('LEFT(hora_fin,5) as hora'), DB::raw('AVG(decibeles) as db'))
                ->groupBy(DB::raw('LEFT(hora_fin,5)'))
                ->orderBy('hora')
                ->get()
                ->map(fn($r) => ['hora' => $r->hora, 'db' => round($r->db, 1)])
                ->values();
        } elseif ($obraId) {
            // Historial ponderado de la obra
            $obra = Obra::find($obraId);
            $titulo = $obra?->nombre;
            $ids = \App\Models\Trabajador::where('obra_id', $obraId)->pluck('id');
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
        if ($obraId)
            $limite = Obra::find($obraId)?->limite_db ?? 85;

        return response()->json(compact('obras', 'trabajadores', 'historial', 'titulo', 'limite'));
    }

}
