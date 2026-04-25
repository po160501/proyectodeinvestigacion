@extends('layouts.app')
@section('title', 'Configuración')
@section('page-title', 'Configuración del Sistema')

@section('content')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="chart-card">
                <div class="chart-title mb-1">
                    <span class="material-icons"
                        style="font-size:18px;vertical-align:middle;color:var(--primary)">tune</span>
                    Parámetros del Sistema IoT
                </div>
                <p class="text-muted mb-4" style="font-size:.82rem">
                    Configura los umbrales de alerta y parámetros de monitoreo según la normativa peruana de ruido laboral
                    (DS-085-2003-PCM).
                </p>

                <form method="POST" action="{{ route('configuracion.update') }}">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <span class="material-icons"
                                style="font-size:16px;vertical-align:middle;color:var(--danger)">warning</span>
                            Límite de Ruido Crítico (dB)
                        </label>
                        <div class="input-group">
                            <input type="number" name="limite_db"
                                class="form-control @error('limite_db') is-invalid @enderror"
                                value="{{ old('limite_db', $config['limite_db']) }}" min="50" max="140" step="1">
                            <span class="input-group-text">dB</span>
                        </div>
                        <div class="form-text">Valor recomendado: 85 dB (OMS / normativa peruana). Se generará alerta
                            automática al superar este valor.</div>
                        @error('limite_db')
                            <div class="text-danger" style="font-size:.82rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <span class="material-icons"
                                style="font-size:16px;vertical-align:middle;color:var(--accent)">schedule</span>
                            Intervalo de Medición (segundos)
                        </label>
                        <div class="input-group">
                            <input type="number" name="intervalo_medicion"
                                class="form-control @error('intervalo_medicion') is-invalid @enderror"
                                value="{{ old('intervalo_medicion', $config['intervalo_medicion']) }}" min="1" max="60">
                            <span class="input-group-text">seg</span>
                        </div>
                        <div class="form-text">Frecuencia con la que el sensor ESP32 envía datos al sistema.</div>
                        @error('intervalo_medicion')
                            <div class="text-danger" style="font-size:.82rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <span class="material-icons" style="font-size:16px;vertical-align:middle">email</span>
                            Correo para Alertas
                        </label>
                        <input type="email" name="email_alertas"
                            class="form-control @error('email_alertas') is-invalid @enderror"
                            value="{{ old('email_alertas', $config['email_alertas']) }}"
                            placeholder="responsable@empresa.com">
                        <div class="form-text">Se notificará a este correo cuando se genere una alerta crítica.</div>
                        @error('email_alertas')
                            <div class="text-danger" style="font-size:.82rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    {{-- Info ESP32 --}}
                    <div class="p-3 rounded mb-4" style="background:#E3F2FD;border-left:4px solid var(--primary)">
                        <div class="fw-semibold mb-2" style="font-size:.9rem">
                            <span class="material-icons" style="font-size:16px;vertical-align:middle">developer_board</span>
                            Endpoint para ESP32 / Arduino
                        </div>
                        <code style="font-size:.8rem;color:#1565C0">
                                POST {{ url('/api/medicion') }}<br>
                                Body: { "sensor_id": 1, "decibeles": 87.5 }
                            </code>
                    </div>

                    <button type="submit" class="btn btn-primary px-4">
                        <span class="material-icons" style="font-size:16px;vertical-align:middle">save</span>
                        Guardar Configuración
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection