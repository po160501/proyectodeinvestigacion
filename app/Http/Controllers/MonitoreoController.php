<?php

namespace App\Http\Controllers;

use App\Models\MedicionRuido;
use App\Models\Sensor;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoreoController extends Controller
{
    public function index()
    {
        $sensores = Sensor::where('estado', 'activo')->get();
        return view('monitoreo.index', compact('sensores'));
    }

    // Endpoint AJAX — retorna últimas mediciones en tiempo real
    public function datos()
    {
        $sensores = Sensor::where('estado', 'activo')->get()->map(function ($s) {
            $ultima = MedicionRuido::where('sensor_id', $s->id)->latest()->first();
            return [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'ubicacion' => $s->ubicacion,
                'decibeles' => $ultima ? $ultima->decibeles : 0,
                'hora' => $ultima ? $ultima->hora : '--:--',
                'critico' => $ultima && $ultima->decibeles >= 85,
            ];
        });

        $historial = MedicionRuido::whereDate('fecha', Carbon::today())
            ->orderBy('hora')
            ->get(['hora', 'decibeles'])
            ->map(fn($m) => ['hora' => substr($m->hora, 0, 5), 'db' => $m->decibeles]);

        return response()->json(['sensores' => $sensores, 'historial' => $historial]);
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
