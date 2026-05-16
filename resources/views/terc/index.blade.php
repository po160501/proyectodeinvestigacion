@extends('layouts.app')

@section('title', 'TERC — Tiempo de Exposición a Ruido Crítico')
@section('page-title', 'TERC — Reporte Detallado')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 mb-1">TERC — Tiempo de Exposición a Ruido Crítico</h1>
                <p class="text-muted small">Análisis de duración acumulada por encima de 85 dB</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('terc.exportar') }}" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                    <span class="material-icons" style="font-size:18px">download</span> Excel
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2">
                    <span class="material-icons" style="font-size:18px">dashboard</span> Volver
                </a>
            </div>
        </div>

        {{-- Gráficos copiados del Dashboard --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="chart-card">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <div class="chart-title mb-0">Exposición Acumulada</div>
                            <div class="chart-sub">Comparativa entre registros del sistema y manuales</div>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="text-center px-3 py-2 rounded"
                                style="background:#f8514922;border:1px solid #f85149">
                                <div class="small text-muted">Sistema</div>
                                <div style="font-size:1.3rem;font-weight:700;color:#f85149">{{ $tercPromedio }}m</div>
                            </div>
                            <div class="text-center px-3 py-2 rounded"
                                style="background:#f0883e22;border:1px solid #f0883e">
                                <div class="small text-muted">Manual</div>
                                <div style="font-size:1.3rem;font-weight:700;color:#f0883e">{{ $tercPromedioManual }}m</div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <canvas id="chartTercDiario" height="150"></canvas>
                        </div>
                        <div class="col-12 col-lg-6">
                            <canvas id="chartTercHisto" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tablas de Datos --}}
        <div class="row g-3">
            <div class="col-12 col-lg-7">
                <div class="table-card">
                    <div class="table-header">
                        <h6 class="mb-0" style="color:var(--danger)">Exposiciones del Sistema (≥85 dB)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Minutos</th>
                                    <th>Prom. dB</th>
                                </tr>
                            </thead>
                            <tbody id="tableTercSistemaBody">
                                @forelse($tercSistema as $t)
                                    <tr>
                                        <td>{{ $t['hora_inicio'] }}</td>
                                        <td>{{ $t['hora_fin'] }}</td>
                                        <td class="text-danger fw-bold">{{ $t['minutos'] }}m</td>
                                        <td>{{ $t['db'] }} dB</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">Sin exposiciones hoy.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="table-card">
                    <div class="table-header">
                        <h6 class="mb-0" style="color:var(--warning)">Registros Manuales</h6>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalTerc">+
                            Manual</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Minutos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tercManual as $t)
                                    <tr>
                                        <td>{{ $t['hora_inicio'] }}</td>
                                        <td>{{ $t['hora_fin'] }}</td>
                                        <td class="fw-bold">{{ $t['minutos'] }}m</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">Sin registros manuales.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal TERC --}}
    <div class="modal fade" id="modalTerc" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('dashboard.terc') }}" method="POST" class="modal-content"
                style="background:#0d1117;border:1px solid #30363d;color:#c9d1d9">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #30363d">
                    <h5 class="modal-title">Registrar Exposición Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="fecha" value="{{ date('Y-m-d') }}">
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="small">Inicio</label><input type="time" name="hora_inicio"
                                class="form-control form-control-sm" required></div>
                        <div class="col"><label class="small">Fin</label><input type="time" name="hora_fin"
                                class="form-control form-control-sm" required></div>
                    </div>
                    <div class="mb-2"><label class="small">dB</label><input type="number" step="0.1" name="decibeles"
                            class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="small">Fuente</label><input type="text" name="fuente"
                            class="form-control form-control-sm"></div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #30363d">
                    <button type="submit" class="btn btn-sm btn-danger">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const tercDias = @json($tercDiario->pluck('dia'));
        const tercSis = @json($tercDiario->pluck('sistema'));
        const tercMan = @json($tercDiario->pluck('manual'));
        const tercMins = @json($tercData->pluck('minutos'));

        const DEMO_DIAS = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie'];

        new Chart(document.getElementById('chartTercDiario'), {
            type: 'bar',
            data: {
                labels: tercDias.length ? tercDias : DEMO_DIAS,
                datasets: [
                    { label: 'Sistema (min)', data: tercSis.length ? tercSis : [15, 22, 18, 30, 25], backgroundColor: 'rgba(248,81,73,.8)', borderRadius: 4 },
                    { label: 'Manual (min)', data: tercMan.length ? tercMan : [10, 15, 12, 20, 18], backgroundColor: 'rgba(240,136,62,.8)', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        const bins = [0, 0, 0, 0, 0];
        const dataForBins = tercMins.length ? tercMins : [5, 15, 25, 35, 45, 8, 22, 38, 12, 42];
        dataForBins.forEach(m => {
            if (m < 10) bins[0]++; else if (m < 20) bins[1]++; else if (m < 30) bins[2]++;
            else if (m < 40) bins[3]++; else bins[4]++;
        });
        new Chart(document.getElementById('chartTercHisto'), {
            type: 'bar',
            data: {
                labels: ['<10 min', '10-20', '20-30', '30-40', '>40 min'],
                datasets: [{
                    label: 'Frecuencia', data: bins,
                    backgroundColor: ['rgba(63,185,80,.8)', 'rgba(63,185,80,.6)', 'rgba(240,136,62,.7)', 'rgba(248,81,73,.7)', 'rgba(248,81,73,.9)'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
@endpush