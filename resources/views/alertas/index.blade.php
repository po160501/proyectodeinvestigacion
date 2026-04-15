@extends('layouts.app')
@section('title', 'Alertas')
@section('page-title', 'Alertas del Sistema')

@section('content')

    {{-- Filtros --}}
    <div class="chart-card mb-4">
        <form method="GET" action="{{ route('alertas.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold" style="font-size:.82rem">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="{{ request('fecha') }}">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold" style="font-size:.82rem">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="resuelta" {{ request('estado') == 'resuelta' ? 'selected' : '' }}>Resuelta</option>
                    <option value="ignorada" {{ request('estado') == 'ignorada' ? 'selected' : '' }}>Ignorada</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="material-icons" style="font-size:16px;vertical-align:middle">search</span> Filtrar
                </button>
                <a href="{{ route('alertas.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="table-card">
        <div class="table-header">
            <h6><span class="material-icons"
                    style="font-size:18px;vertical-align:middle;color:#E53935">notifications_active</span>
                Registro de Alertas</h6>
            <span class="badge bg-danger">{{ $alertas->total() }} alertas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Sensor</th>
                        <th>Ubicación</th>
                        <th>Nivel Ruido</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alertas as $alerta)
                        <tr>
                            <td class="text-muted">{{ $alerta->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($alerta->fecha)->format('d/m/Y') }}</td>
                            <td>{{ substr($alerta->hora, 0, 5) }}</td>
                            <td>{{ $alerta->sensor->nombre ?? 'N/A' }}</td>
                            <td>{{ $alerta->sensor->ubicacion ?? '—' }}</td>
                            <td>
                                <span
                                    class="db-value fw-bold {{ $alerta->nivel_ruido >= 100 ? 'db-critical' : 'db-warning' }}">
                                    {{ $alerta->nivel_ruido }} dB
                                </span>
                            </td>
                            <td>
                                <span class="badge-custom badge-{{ $alerta->estado }}">
                                    {{ ucfirst($alerta->estado) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('alertas.estado', $alerta) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <select name="estado" class="form-select form-select-sm d-inline-block w-auto"
                                        onchange="this.form.submit()">
                                        <option value="activa" {{ $alerta->estado == 'activa' ? 'selected' : '' }}>Activa
                                        </option>
                                        <option value="resuelta" {{ $alerta->estado == 'resuelta' ? 'selected' : '' }}>
                                            Resuelta</option>
                                        <option value="ignorada" {{ $alerta->estado == 'ignorada' ? 'selected' : '' }}>
                                            Ignorada</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay alertas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $alertas->withQueryString()->links() }}</div>
    </div>

@endsection
