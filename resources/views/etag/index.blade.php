@extends('layouts.app')

@section('title', 'ETAG — Tiempo de Respuesta de Alertas')
@section('page-title', 'ETAG — Reporte Detallado')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 mb-1">ETAG — Tiempo de Respuesta de Alertas</h1>
                <p class="text-muted small">Análisis de velocidad de notificación del sistema</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2">
                <span class="material-icons" style="font-size:18px">dashboard</span> Volver
            </a>
        </div>

        {{-- Gráficos copiados del Dashboard --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="chart-card">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <div class="chart-title mb-0">Tiempos de Respuesta (Segundos)</div>
                            <div class="chart-sub">Diferencia entre detección de ruido y emisión de alerta</div>
                        </div>
                        <div class="text-center px-3 py-2 rounded" style="background:#f0883e22;border:1px solid #f0883e">
                            <div class="small text-muted">Respuesta prom.</div>
                            <div style="font-size:1.5rem;font-weight:700;color:#f0883e">
                                {{ $etagPromedio > 0 ? $etagPromedio . 's' : 'Sin datos' }}
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <canvas id="chartEtagBarras" height="150"></canvas>
                        </div>
                        <div class="col-12 col-lg-6">
                            <canvas id="chartEtagLinea" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tablas de Datos --}}
        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="table-card">
                    <div class="table-header">
                        <h6 class="mb-0" style="color:var(--primary)">Alertas Automáticas (Sistema)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Alerta</th>
                                    <th>Respuesta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($etagData->where('fuente', 'sistema') as $e)
                                    <tr class="{{ $e['alto'] ? 'table-danger' : '' }}">
                                        <td>{{ $e['hora_evento'] }}</td>
                                        <td>{{ $e['hora_alerta'] }}</td>
                                        <td class="{{ $e['alto'] ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                            {{ $e['segundos'] }}s
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">Sin alertas de sistema hoy.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="table-card">
                    <div class="table-header">
                        <h6 class="mb-0" style="color:var(--warning)">Registros Manuales</h6>
                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEtag">+
                            Manual</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Alerta</th>
                                    <th>Respuesta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($etagData->where('fuente', 'manual') as $e)
                                    <tr>
                                        <td>{{ $e['hora_evento'] }}</td>
                                        <td>{{ $e['hora_alerta'] }}</td>
                                        <td class="fw-bold">{{ $e['segundos'] }}s</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">Sin registros manuales hoy.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal ETAG --}}
    <div class="modal fade" id="modalEtag" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('dashboard.etag') }}" method="POST" class="modal-content"
                style="background:#0d1117;border:1px solid #30363d;color:#c9d1d9">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #30363d">
                    <h5 class="modal-title">Registrar Evento Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="fecha" value="{{ date('Y-m-d') }}">
                    <div class="mb-2"><label class="small">Hora Evento</label><input type="time" name="hora_evento"
                            class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="small">Hora Alerta</label><input type="time" name="hora_alerta"
                            class="form-control form-control-sm" required></div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #30363d">
                    <button type="submit" class="btn btn-sm btn-warning text-white">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const etagData = @json($etagData);
        const etagSeg = etagData.length ? etagData.map(e => e.segundos) : [5, 12, 8, 20, 15];
        const etagFuentes = etagData.length ? etagData.map(e => e.fuente) : ['sistema', 'manual', 'sistema', 'manual', 'sistema'];
        const etagLabels = etagData.length ? etagData.map(e => e.hora_evento) : ['E1', 'E2', 'E3', 'E4', 'E5'];

        new Chart(document.getElementById('chartEtagBarras'), {
            type: 'bar',
            data: {
                labels: etagLabels,
                datasets: [{
                    label: 'Tiempo respuesta (s)',
                    data: etagSeg,
                    backgroundColor: etagFuentes.map(f => f === 'sistema' ? 'rgba(31,111,235,.8)' : 'rgba(240,136,62,.8)'),
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        new Chart(document.getElementById('chartEtagLinea'), {
            type: 'line',
            data: {
                labels: etagLabels,
                datasets: [{
                    label: 'Tendencia (s)', data: etagSeg,
                    borderColor: '#f0883e', backgroundColor: 'rgba(240,136,62,.1)',
                    borderWidth: 2, pointRadius: 4, tension: 0.4, fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
@endpush