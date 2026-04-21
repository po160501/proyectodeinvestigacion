<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\ExposicionRuido;
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
            'decibeles'    => 'required|numeric|min:0|max:200',
            'hora_inicio'  => 'required|date_format:H:i:s',
            'hora_fin'     => 'required|date_format:H:i:s',
        ]);

        $trabajador = Trabajador::where('token_sesion', $token)->with('obra')->firstOrFail();

        $inicio = Carbon::createFromFormat('H:i:s', $request->hora_inicio);
        $fin    = Carbon::createFromFormat('H:i:s', $request->hora_fin);
        $minutos = max(1, $inicio->diffInMinutes($fin));

        ExposicionRuido::create([
            'trabajador_id'    => $trabajador->id,
            'hora_inicio'      => $request->hora_inicio,
            'hora_fin'         => $request->hora_fin,
            'tiempo_exposicion'=> $minutos,
            'decibeles'        => $request->decibeles,
            'fecha'            => now()->toDateString(),
        ]);

        // Verificar límite y notificar si aplica
        $limite = $trabajador->obra?->limite_db ?? 85;
        $alerta = $request->decibeles >= $limite;

        return response()->json(['ok' => true, 'alerta' => $alerta, 'limite' => $limite]);
    }
}
