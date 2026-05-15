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
        // Guardamos el token en una cookie por 1 año para persistencia en PWA
        cookie()->queue('worker_token', $token, 60 * 24 * 365);
        return view('trabajador.medidor', compact('trabajador', 'token'));
    }

    // Guardar nombre y teléfono (primer acceso o actualización)
    public function registrar(Request $request, string $token)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'jornada_inicio' => 'nullable|date_format:H:i',
            'jornada_fin' => 'nullable|date_format:H:i',
        ]);

        $trabajador = Trabajador::where('token_sesion', $token)->firstOrFail();
        $trabajador->update($request->only('nombre', 'telefono', 'jornada_inicio', 'jornada_fin'));

        return response()->json(['ok' => true, 'nombre' => $trabajador->nombre]);
    }

    // Guardar medición de decibeles desde el celular
    public function guardarMedicion(Request $request, string $token)
    {
        $request->validate([
            'decibeles' => 'required|numeric|min:0|max:200',
            'hora_inicio' => ['required', 'regex:/^\d{2}:\d{2}:\d{2}(\.\d+)?$/'],
            'hora_fin'   => ['required', 'regex:/^\d{2}:\d{2}:\d{2}(\.\d+)?$/'],
        ]);

        $trabajador = Trabajador::where('token_sesion', $token)->with('obra')->firstOrFail();
        $limite = $trabajador->obra?->limite_db ?? 85;
        $alerta = $request->decibeles >= $limite;

        // Parsear con o sin milisegundos
        $fmtInicio = str_contains($request->hora_inicio, '.') ? 'H:i:s.u' : 'H:i:s';
        $fmtFin    = str_contains($request->hora_fin, '.') ? 'H:i:s.u' : 'H:i:s';
        $inicio = Carbon::createFromFormat($fmtInicio, $request->hora_inicio);
        $fin    = Carbon::createFromFormat($fmtFin, $request->hora_fin);
        $minutos = max(1, (int) $inicio->diffInMinutes($fin));

        ExposicionRuido::create([
            'trabajador_id' => $trabajador->id,
            'hora_inicio'   => $inicio->format('H:i:s.v'),   // guarda con ms
            'hora_fin'      => $fin->format('H:i:s.v'),      // guarda con ms
            'tiempo_exposicion' => $minutos,
            'decibeles'     => $request->decibeles,
            'fecha'         => now()->toDateString(),
        ]);

        // Crear alerta inmediata si supera límite
        if ($alerta) {
            Alerta::create([
                'sensor_id'    => null,
                'trabajador_id'=> $trabajador->id,
                'obra_id'      => $trabajador->obra_id,
                'nivel_ruido'  => $request->decibeles,
                'fecha'        => now()->toDateString(),
                'hora'         => now()->format('H:i:s.v'), // Tiempo de recepción real con milisegundos
                'estado'       => 'activa',
            ]);
        }

        return response()->json([
            'ok' => true,
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
