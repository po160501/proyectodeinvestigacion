<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ExposicionRuido;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrabajadorController extends Controller
{
    // Vista del medidor (acceso por token, sin auth)
    public function medidor(string $token)
    {
        $trabajador = Trabajador::where('token_sesion', $token)->with('obra')->firstOrFail();
        return view('trabajador.medidor', compact('trabajador', 'token'));
    }

    // Guardar nombre y teléfono (primer acceso o actualización)
    public function registrar(Request $request, string $token)
    {
        $request->validate([
            'nombre'    => 'required|string|max:100',
            'telefono'  => 'nullable|string|max:20',
            'jornada_inicio' => 'nullable|date_format:H:i',
            'jornada_fin'    => 'nullable|date_format:H:i',
        ]);

        $trabajador = Trabajador::where('token_sesion', $token)->firstOrFail();
        $trabajador->update($request->only('nombre', 'telefono', 'jornada_inicio', 'jornada_fin'));

        return response()->json(['ok' => true, 'nombre' => $trabajador->nombre]);
    }

    // Guardar medición de decibeles desde el celular
    public function guardarMedicion(Request $request, string $token)
    {
        $request->validate([
            'decibeles'   => 'required|numeric|min:0|max:200',
            'hora_inicio' => 'required|date_format:H:i:s',
            'hora_fin'    => 'required|date_format:H:i:s',
        ]);

        $trabajador = Trabajador::where('token_sesion', $token)->with('obra')->firstOrFail();
        $limite     = $trabajador->obra?->limite_db ?? 85;
        $alerta     = $request->decibeles >= $limite;

        $inicio  = Carbon::createFromFormat('H:i:s', $request->hora_inicio);
        $fin     = Carbon::createFromFormat('H:i:s', $request->hora_fin);
        $minutos = max(1, (int) $inicio->diffInMinutes($fin));

        ExposicionRuido::create([
            'trabajador_id'     => $trabajador->id,
            'hora_inicio'       => $request->hora_inicio,
            'hora_fin'          => $request->hora_fin,
            'tiempo_exposicion' => $minutos,
            'decibeles'         => $request->decibeles,
            'fecha'             => now()->toDateString(),
        ]);

        // Crear alerta inmediata si supera límite
        if ($alerta) {
            // Evitar duplicados: no crear si ya hay una alerta del mismo trabajador en los últimos 30s
            $reciente = Alerta::where('trabajador_id', $trabajador->id)
                ->where('created_at', '>=', now()->subSeconds(30))
                ->exists();

            if (!$reciente) {
                Alerta::create([
                    'sensor_id'     => null,
                    'trabajador_id' => $trabajador->id,
                    'obra_id'       => $trabajador->obra_id,
                    'nivel_ruido'   => $request->decibeles,
                    'fecha'         => now()->toDateString(),
                    'hora'          => now()->toTimeString(),
                    'estado'        => 'activa',
                ]);
            }
        }

        return response()->json([
            'ok'     => true,
            'alerta' => $alerta,
            'limite' => $limite,
        ]);
    }

    public function destroy(Trabajador $trabajador)
    {
        $trabajador->delete();
        return back()->with('success', 'Trabajador eliminado.');
    }
}
