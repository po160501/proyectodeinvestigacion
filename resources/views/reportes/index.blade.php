@extends('layouts.app')
@section('title', 'Reportes')
@section('page-title', 'Reportes del Sistema')

@section('content')

    {{-- Selector de tipo --}}
    <div class="chart-card mb-4">
        <div class="chart-title mb-3">Generar Reporte</div>
        <form method="GET" action="{{ route('reportes.generar') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold" style="font-size:.82rem">Tipo de Reporte</label>
                <select name="tipo" class="form-select">
                    <option value="diario" {{ (request('tipo') ?? 'diario') == 'diario' ? 'selected' : '' }}>Diario (Hoy)
                    </option>
                    <option value="semanal" {{ request('tipo') == 'semanal' ? 'selected' : '' }}>Semanal</option>
                    <option value="mensual" {{ request('tipo') == 'mensual' ? 'selected' : '' }}>Mensual</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size:16px;vertical-align:middle">assessment</span>
                    Generar Reporte
                </button>
            </div>
        </form>
    </div>

    @isset($resumen)
        {{-- Resumen estadístico --}}
        <div class="row g-3 mb-4">
            @php
                $cards = [
                    [
                        'label' => 'Promedio dB',
                        'value' => $resumen['promedio_db'] . ' dB',
                        'icon' => 'volume_up',
                        'color' => 'bg-blue',
                    ],
                    [
                        'label' => 'Máximo dB',
                        'value' => $resumen['maximo_db'] . ' dB',
                        'icon' => 'trending_up',
                        'color' => 'bg-red',
                    ],
                    [
                        'label' => 'Mínimo dB',
                        'value' => $resumen['minimo_db'] . ' dB',
                        'icon' => 'trending_down',
                        'color' => 'bg-teal',
                    ],
                    [
                        'label' => 'Total Alertas',
                        'value' => $resumen['total_alertas'],
                        'icon' => 'notifications',
                        'color' => 'bg-red',
                    ],
                    [
                        'label' => 'Total Mediciones',
                        'value' => $resumen['total_mediciones'],
                        'icon' => 'data_usage',
                        'color' => 'bg-blue',
                    ],
                    [
                        'label' => 'Tiempo Prom. Exp.',
                        'value' => $resumen['tiempo_promedio'] . ' min',
                        'icon' => 'timer',
                        'color' => 'bg-orange',
                    ],
                ];
            @endphp
            @foreach ($cards as $c)
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="metric-card">
                        <div class="metric-icon {{ $c['color'] }}" style="width:44px;height:44px">
                            <span class="material-icons" style="font-size:20px">{{ $c['icon'] }}</span>
                        </div>
                        <div>
                            <div class="metric-value" style="font-size:1.3rem">{{ $c['value'] }}</div>
                            <div class="metric-label">{{ $c['label'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Período --}}
        <div class="alert alert-success mb-4">
            <span class="material-icons" style="font-size:16px;vertical-align:middle">date_range</span>
            Reporte <strong>{{ ucfirst($tipo) }}</strong> —
            {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}
        </div>

        {{-- Tabla mediciones --}}
        <div class="table-card mb-4">
            <div class="table-header">
                <h6>Mediciones de Ruido</h6>
                <span class="badge bg-primary">{{ $mediciones->count() }} registros</span>
            </div>
            <div class="table-responsive" style="max-height:300px;overflow-y:auto">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Sensor</th>
                            <th>Decibeles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mediciones->take(50) as $m)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') }}</td>
                                <td>{{ substr($m->hora, 0, 5) }}</td>
                                <td>{{ $m->sensor->nombre ?? 'N/A' }}</td>
                                <td><span
                                        class="fw-bold {{ $m->decibeles >= 85 ? 'db-critical' : 'db-normal' }}">{{ $m->decibeles }}
                                        dB</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Sin mediciones en este período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabla alertas --}}
        <div class="table-card">
            <div class="table-header">
                <h6>Alertas del Período</h6>
                <span class="badge bg-danger">{{ $alertas->count() }} alertas</span>
            </div>
            <div class="table-responsive" style="max-height:300px;overflow-y:auto">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Sensor</th>
                            <th>Nivel</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alertas as $a)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($a->fecha)->format('d/m/Y') }}</td>
                                <td>{{ substr($a->hora, 0, 5) }}</td>
                                <td>{{ $a->sensor->nombre ?? 'N/A' }}</td>
                                <td><span class="fw-bold db-critical">{{ $a->nivel_ruido }} dB</span></td>
                                <td><span class="badge-custom badge-{{ $a->estado }}">{{ ucfirst($a->estado) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Sin alertas en este período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endisset

@endsection