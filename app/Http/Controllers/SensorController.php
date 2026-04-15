<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function index()
    {
        $sensores = Sensor::latest()->get();
        return view('sensores.index', compact('sensores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'required|string|max:200',
            'estado' => 'required|in:activo,inactivo,mantenimiento',
        ]);
        Sensor::create($request->only('nombre', 'ubicacion', 'estado'));
        return back()->with('success', 'Sensor registrado correctamente.');
    }

    public function update(Request $request, Sensor $sensor)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'required|string|max:200',
            'estado' => 'required|in:activo,inactivo,mantenimiento',
        ]);
        $sensor->update($request->only('nombre', 'ubicacion', 'estado'));
        return back()->with('success', 'Sensor actualizado.');
    }

    public function destroy(Sensor $sensor)
    {
        $sensor->delete();
        return back()->with('success', 'Sensor eliminado.');
    }
}
