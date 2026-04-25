<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Obra;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ObraController extends Controller
{
    public function index()
    {
        $obras = Obra::with(['areas', 'trabajadores'])->withCount('trabajadores')->get();
        return view('obras.index', compact('obras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'limite_db' => 'integer|min:50|max:140',
            'areas' => 'required|array|min:1',
            'areas.*' => 'required|string|max:100',
        ]);

        $obra = Obra::create($request->only('nombre', 'descripcion', 'limite_db'));

        foreach (array_filter($request->areas) as $nombreArea) {
            Area::create(['obra_id' => $obra->id, 'nombre' => trim($nombreArea)]);
        }

        return back()->with('success', 'Obra creada con áreas.');
    }

    public function destroy(Obra $obra)
    {
        $obra->delete();
        return back()->with('success', 'Obra eliminada.');
    }

    // Asignar área a un trabajador (AJAX)
    public function asignarArea(Request $request, Trabajador $trabajador)
    {
        $request->validate(['area_id' => 'nullable|exists:areas,id']);
        $trabajador->update(['area_id' => $request->area_id ?: null]);
        return response()->json(['ok' => true]);
    }

    // Genera token para trabajador
    public function generarToken(Obra $obra)
    {
        $token = Str::random(32);
        Trabajador::create([
            'nombre' => 'Pendiente',
            'empresa' => '',
            'area' => $obra->nombre,
            'obra_id' => $obra->id,
            'token_sesion' => $token,
        ]);
        return response()->json(['url' => route('trabajador.medidor', $token), 'token' => $token]);
    }
}
