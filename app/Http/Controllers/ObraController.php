<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\Trabajador;
use App\Models\ExposicionRuido;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ObraController extends Controller
{
    public function index()
    {
        $obras = Obra::withCount('trabajadores')->with('trabajadores')->get();
        return view('obras.index', compact('obras'));
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:100', 'limite_db' => 'integer|min:50|max:140']);
        Obra::create($request->only('nombre', 'descripcion', 'limite_db'));
        return back()->with('success', 'Obra/Área creada.');
    }

    public function destroy(Obra $obra)
    {
        $obra->delete();
        return back()->with('success', 'Obra eliminada.');
    }

    // Genera un token único para que un trabajador acceda sin cuenta
    public function generarToken(Obra $obra)
    {
        $token = Str::random(32);
        // Crear trabajador "placeholder" con token, sin nombre aún
        $trabajador = Trabajador::create([
            'nombre'       => 'Pendiente',
            'empresa'      => '',
            'area'         => $obra->nombre,
            'obra_id'      => $obra->id,
            'token_sesion' => $token,
        ]);
        $url = route('trabajador.medidor', ['token' => $token]);
        return response()->json(['url' => $url, 'token' => $token]);
    }
}
