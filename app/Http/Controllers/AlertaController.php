<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Obra;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $query = Alerta::with(['trabajador.obra', 'obra'])
            ->whereNotNull('trabajador_id')
            ->where('nivel_ruido', '>=', 85)
            ->latest();

        if ($request->filled('fecha'))
            $query->whereDate('fecha', $request->fecha);
        if ($request->filled('estado'))
            $query->where('estado', $request->estado);
        if ($request->filled('obra_id'))
            $query->where('obra_id', $request->obra_id);

        $alertas = $query->paginate(20);
        $obras = Obra::orderBy('nombre')->get();

        return view('alertas.index', compact('alertas', 'obras'));
    }

    public function updateEstado(Request $request, Alerta $alerta)
    {
        $alerta->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado.');
    }
}
