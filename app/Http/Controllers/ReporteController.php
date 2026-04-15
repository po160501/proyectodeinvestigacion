<?php

namespace App\Http\Controllers;

use App\Models\MedicionRuido;
use App\Models\Alerta;
use App\Models\ExposicionRuido;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function generar(Request $request)
    {
        $tipo = $request->get('tipo', 'diario');

        [$inicio, $fin] = match ($tipo) {
            'semanal' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'mensual' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::today(), Carbon::today()],
        };

        $mediciones = MedicionRuido::with('sensor')
            ->whereBetween('fecha', [$inicio, $fin])
            ->orderBy('fecha')->orderBy('hora')
            ->get();

        $alertas = Alerta::with('sensor')
            ->whereBetween('fecha', [$inicio, $fin])
            ->orderBy('fecha')->orderBy('hora')
            ->get();

        $exposiciones = ExposicionRuido::with('trabajador')
            ->whereBetween('fecha', [$inicio, $fin])
            ->get();

        $resumen = [
            'promedio_db' => round($mediciones->avg('decibeles') ?? 0, 1),
            'maximo_db' => round($mediciones->max('decibeles') ?? 0, 1),
            'minimo_db' => round($mediciones->min('decibeles') ?? 0, 1),
            'total_alertas' => $alertas->count(),
            'total_mediciones' => $mediciones->count(),
            'tiempo_promedio' => round($exposiciones->avg('tiempo_exposicion') ?? 0, 1),
        ];

        return view('reportes.index', compact('mediciones', 'alertas', 'exposiciones', 'resumen', 'tipo', 'inicio', 'fin'));
    }
}
