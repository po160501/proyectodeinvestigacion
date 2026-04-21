<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\MedicionRuido;
use App\Models\Sensor;
use App\Models\ExposicionRuido;
use App\Models\Obra;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        // Tarjetas
        $nivelPromedio = MedicionRuido::whereDate('fecha', $hoy)->avg('decibeles') ?? 0;
        $alertasHoy = Alerta::whereDate('fecha', $hoy)->count();
        $tiempoPromedio = ExposicionRuido::whereDate('fecha', $hoy)->avg('tiempo_exposicion') ?? 0;
        $sensoresActivos = Sensor::where('estado', 'activo')->count();

        // Gráfico 1: Niveles de ruido por hora (hoy)
        $ruidoPorHora = MedicionRuido::whereDate('fecha', $hoy)
            ->select(DB::raw('HOUR(hora) as hora'), DB::raw('AVG(decibeles) as promedio'))
            ->groupBy(DB::raw('HOUR(hora)'))
            ->orderBy('hora')
            ->get()
            ->map(fn($r) => ['hora' => sprintf('%02d:00', $r->hora), 'db' => round($r->promedio, 1)]);

        // Gráfico 2: Alertas por día (últimos 7 días)
        $alertasPorDia = Alerta::select(DB::raw('DATE(fecha) as dia'), DB::raw('COUNT(*) as total'))
            ->where('fecha', '>=', Carbon::now()->subDays(6))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(fn($r) => ['dia' => Carbon::parse($r->dia)->locale('es')->isoFormat('ddd D/M'), 'total' => $r->total]);

        // Gráfico 3: Exposición por trabajador (hoy)
        $exposicionPorTrabajador = ExposicionRuido::with('trabajador')
            ->whereDate('fecha', $hoy)
            ->select('trabajador_id', DB::raw('SUM(tiempo_exposicion) as total_min'), DB::raw('AVG(decibeles) as avg_db'))
            ->groupBy('trabajador_id')
            ->get()
            ->map(fn($r) => [
                'nombre' => $r->trabajador->nombre ?? 'N/A',
                'minutos' => $r->total_min,
                'db' => round($r->avg_db, 1),
            ]);

        // Gráfico 4: Comparación antes vs después
        $comparacion = [
            'antes' => ['exposicion' => 58, 'alertas' => 42, 'precision' => 61],
            'despues' => [
                'exposicion' => round(ExposicionRuido::avg('tiempo_exposicion') ?? 24, 1),
                'alertas' => Alerta::count(),
                'precision' => 94,
            ],
        ];

        // Obras con minutos acumulados sobre el límite (hoy)
        $obrasSobreLimite = Obra::with(['trabajadores.exposiciones' => function ($q) use ($hoy) {
            $q->whereDate('fecha', $hoy);
        }])->get()->map(function ($obra) {
            $exposiciones = $obra->trabajadores->flatMap->exposiciones;
            $minSobre = $exposiciones->where('decibeles', '>=', $obra->limite_db)->sum('tiempo_exposicion');
            $minTotal = $exposiciones->sum('tiempo_exposicion');
            $avgDb    = $exposiciones->avg('decibeles') ?? 0;
            return [
                'obra'      => $obra->nombre,
                'limite_db' => $obra->limite_db,
                'min_sobre' => (int) $minSobre,
                'min_total' => (int) $minTotal,
                'avg_db'    => round($avgDb, 1),
                'trabajadores' => $obra->trabajadores->count(),
            ];
        })->filter(fn($o) => $o['min_sobre'] > 0)->sortByDesc('min_sobre')->values();

        // Exposición por obra (hoy)
        $exposicionPorObra = Obra::with(['trabajadores.exposiciones' => function($q) use ($hoy) {
            $q->whereDate('fecha', $hoy);
        }])->get()->map(function($obra) {
            $totalMin = $obra->trabajadores->flatMap->exposiciones->sum('tiempo_exposicion');
            $avgDb    = $obra->trabajadores->flatMap->exposiciones->avg('decibeles') ?? 0;
            return ['obra' => $obra->nombre, 'minutos' => $totalMin, 'db' => round($avgDb, 1)];
        })->filter(fn($o) => $o['minutos'] > 0)->values();

        return view('dashboard', compact(
            'nivelPromedio',
            'alertasHoy',
            'tiempoPromedio',
            'sensoresActivos',
            'ruidoPorHora',
            'alertasPorDia',
            'exposicionPorTrabajador',
            'comparacion',
            'exposicionPorObra',
            'obrasSobreLimite'
        ));
    }
}
