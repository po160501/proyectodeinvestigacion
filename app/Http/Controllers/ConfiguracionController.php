<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = [
            'limite_db' => DB::table('configuraciones')->where('clave', 'limite_db')->value('valor') ?? 85,
            'intervalo_medicion' => DB::table('configuraciones')->where('clave', 'intervalo_medicion')->value('valor') ?? 5,
            'email_alertas' => DB::table('configuraciones')->where('clave', 'email_alertas')->value('valor') ?? '',
        ];
        return view('configuracion.index', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'limite_db' => 'required|numeric|min:50|max:140',
            'intervalo_medicion' => 'required|integer|min:1|max:60',
            'email_alertas' => 'nullable|email',
        ]);

        foreach (['limite_db', 'intervalo_medicion', 'email_alertas'] as $clave) {
            DB::table('configuraciones')->updateOrInsert(
                ['clave' => $clave],
                ['valor' => $request->$clave, 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Configuración guardada.');
    }
}
