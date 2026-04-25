@extends('layouts.app')
@section('title', 'Alertas')
@section('page-title', 'Alertas de Trabajadores')

@section('content')

    <div class="chart-card mb-4">
        <form method="GET" action="{{ route('alertas.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.82rem">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="{{ request('fecha') }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.82rem">Obra</label>
                <select name="obra_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($obras as $obra)
                        <option value="{{ $obra->id }}" {{ request('obra_id') == $obra->id ? 'selected' : '' }}>
                            {{ $obra->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.82rem">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="resuelta" {{ request('estado') == 'resuelta' ? 'selected' : '' }}>Resuelta</option>
                    <option value="ignorada" {{ request('estado') == 'ignorada' ? 'selected' : '' }}>Ignorada</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="material-icons" style="font-size:16px;vertical-align:middle">search</span> Filtrar
                </button>
                <a href="{{ route('alertas.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h6>
                <span class="material-icons"
                    style="font-size:18px;vertical-align:middle;color:#E53935">notifications_active</span>
                Alertas ≥ 85 dB
            </h6>
            <span class="badge bg-danger">{{ $alertas->total() }} alertas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Trabajador</th>
                        <th>Obra</th>
                        <th>Nivel</th>
                        <!-- <th>Estado</th> -->
                        <!-- <th>Acción</th> -->
                    </tr>
                </thead>
                <tbody>
                    @forelse($alertas as $alerta)
                        <tr>
                            <td class="text-muted">{{ $alerta->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($alerta->fecha)->format('d/m/Y') }}</td>
                            <td>{{ substr($alerta->hora, 0, 5) }}</td>
                            <td class="fw-semibold">{{ $alerta->trabajador->nombre ?? '—' }}</td>
                            <td>{{ $alerta->obra->nombre ?? $alerta->trabajador?->obra?->nombre ?? '—' }}</td>
                            <td>
                                <span class="fw-bold {{ $alerta->nivel_ruido >= 100 ? 'text-danger' : 'text-warning' }}">
                                    {{ $alerta->nivel_ruido }} dB
                                </span>
                            </td>
                            <!-- <td>
                                    <span class="badge-custom badge-{{ $alerta->estado }}">{{ ucfirst($alerta->estado) }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('alertas.estado', $alerta) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <select name="estado" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="activa"   {{ $alerta->estado=='activa'   ? 'selected':'' }}>Activa</option>
                                            <option value="resuelta" {{ $alerta->estado=='resuelta' ? 'selected':'' }}>Resuelta</option>
                                            <option value="ignorada" {{ $alerta->estado=='ignorada' ? 'selected':'' }}>Ignorada</option>
                                        </select>
                                    </form>
                                </td> -->
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay alertas ≥ 85 dB registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $alertas->withQueryString()->links() }}</div>
    </div>

@endsection