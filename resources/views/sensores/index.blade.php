@extends('layouts.app')
@section('title', 'Sensores')
@section('page-title', 'Gestión de Sensores IoT')

@section('content')

    <div class="row g-4">
        {{-- Formulario --}}
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-title mb-3">
                    <span class="material-icons"
                        style="font-size:18px;vertical-align:middle;color:var(--primary)">add_circle</span>
                    Registrar Sensor
                </div>
                <form method="POST" action="{{ route('sensores.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.82rem">Nombre del Sensor</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                            placeholder="Ej: Sensor-01" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.82rem">Ubicación</label>
                        <input type="text" name="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror"
                            placeholder="Ej: Área de producción A" value="{{ old('ubicacion') }}" required>
                        @error('ubicacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.82rem">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <span class="material-icons" style="font-size:16px;vertical-align:middle">save</span>
                        Guardar Sensor
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="col-12 col-lg-8">
            <div class="table-card">
                <div class="table-header">
                    <h6><span class="material-icons"
                            style="font-size:18px;vertical-align:middle;color:var(--accent)">device_hub</span>
                        Sensores Registrados</h6>
                    <span class="badge bg-primary">{{ $sensores->count() }} sensores</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Nivel Actual</th>
                                <th>Registrado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sensores as $sensor)
                                <tr>
                                    <td class="text-muted">{{ $sensor->id }}</td>
                                    <td class="fw-semibold">{{ $sensor->nombre }}</td>
                                    <td>{{ $sensor->ubicacion }}</td>
                                    <td>
                                        <span
                                            class="badge-custom
                                    {{ $sensor->estado == 'activo' ? 'badge-activo' : ($sensor->estado == 'inactivo' ? 'badge-inactivo' : 'badge-mant') }}">
                                            {{ ucfirst($sensor->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="fw-bold {{ $sensor->nivel_actual >= 85 ? 'db-critical' : ($sensor->nivel_actual >= 70 ? 'db-warning' : 'db-normal') }}">
                                            {{ $sensor->nivel_actual }} dB
                                        </span>
                                    </td>
                                    <td class="text-muted" style="font-size:.8rem">
                                        {{ \Carbon\Carbon::parse($sensor->fecha_registro)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $sensor->id }}">
                                            <span class="material-icons" style="font-size:15px">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('sensores.destroy', $sensor) }}"
                                            class="d-inline" onsubmit="return confirm('¿Eliminar este sensor?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <span class="material-icons" style="font-size:15px">delete</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal editar --}}
                                <div class="modal fade" id="editModal{{ $sensor->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Sensor</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('sensores.update', $sensor) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold"
                                                            style="font-size:.82rem">Nombre</label>
                                                        <input type="text" name="nombre" class="form-control"
                                                            value="{{ $sensor->nombre }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold"
                                                            style="font-size:.82rem">Ubicación</label>
                                                        <input type="text" name="ubicacion" class="form-control"
                                                            value="{{ $sensor->ubicacion }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold"
                                                            style="font-size:.82rem">Estado</label>
                                                        <select name="estado" class="form-select">
                                                            <option value="activo"
                                                                {{ $sensor->estado == 'activo' ? 'selected' : '' }}>
                                                                Activo</option>
                                                            <option value="inactivo"
                                                                {{ $sensor->estado == 'inactivo' ? 'selected' : '' }}>
                                                                Inactivo</option>
                                                            <option value="mantenimiento"
                                                                {{ $sensor->estado == 'mantenimiento' ? 'selected' : '' }}>
                                                                Mantenimiento</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Guardar
                                                        cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No hay sensores registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
