@extends('layouts.app')
@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')

@section('content')

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-title mb-3">
                    <span class="material-icons"
                        style="font-size:18px;vertical-align:middle;color:var(--primary)">person_add</span>
                    Nuevo Usuario
                </div>
                <form method="POST" action="{{ route('usuarios.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.82rem">Nombre completo</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.82rem">Correo electrónico</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.82rem">Contraseña</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.82rem">Rol</label>
                        <select name="rol" class="form-select">
                            <option value="visualizador">Visualizador</option>
                            <option value="operador">Operador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <span class="material-icons" style="font-size:16px;vertical-align:middle">save</span>
                        Crear Usuario
                    </button>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="table-card">
                <div class="table-header">
                    <h6><span class="material-icons" style="font-size:18px;vertical-align:middle">group</span> Usuarios del
                        Sistema</h6>
                    <span class="badge bg-primary">{{ $usuarios->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Registrado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $u)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div
                                                style="width:32px;height:32px;border-radius:50%;background:var(--primary);
                                                display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-weight:700;font-size:.8rem">
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            </div>
                                            {{ $u->name }}
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $u->email }}</td>
                                    <td>
                                        @php
                                            $rolColor = [
                                                'admin' => 'bg-primary',
                                                'operador' => 'bg-warning text-dark',
                                                'visualizador' => 'bg-secondary',
                                            ];
                                        @endphp
                                        <span
                                            class="badge {{ $rolColor[$u->rol] ?? 'bg-secondary' }}">{{ ucfirst($u->rol) }}</span>
                                    </td>
                                    <td class="text-muted" style="font-size:.8rem">{{ $u->created_at->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        @if ($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}"
                                                onsubmit="return confirm('¿Eliminar usuario {{ $u->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <span class="material-icons" style="font-size:15px">delete</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted" style="font-size:.75rem">Tú</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
