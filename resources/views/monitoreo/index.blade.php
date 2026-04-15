@extends('layouts.app')
@section('title', 'Monitoreo en Tiempo Real')
@section('page-title', 'Monitoreo en Tiempo Real')

@section('content')

    <div class="row g-3 mb-4" id="sensoresGrid">
        @forelse($sensores as $sensor)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card" id="sensor-card-{{ $sensor->id }}">
                    <div class="metric-icon {{ $sensor->nivel_actual >= 85 ? 'bg-red' : 'bg-teal' }}"
                        id="icon-{{ $sensor->id }}">
                        <span class="material-icons">sensors</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="metric-value db-value {{ $sensor->nivel_actual >= 85 ? 'db-critical' : ($sensor->nivel_actual >= 70 ? 'db-warning' : 'db-normal') }}"
                            id="db-{{ $sensor->id }}">
                            {{ $sensor->nivel_actual }} <small style="font-size:.9rem">dB</small>
                        </div>
                        <div class="metric-label">{{ $sensor->nombre }}</div>
                        <div class="metric-sub text-muted">
                            <span class="material-icons" style="font-size:13px;vertical-align:middle">location_on</span>
                            {{ $sensor->ubicacion }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No hay sensores activos registrados.</div>
            </div>
        @endforelse
    </div>

    <div class="chart-card">
        <div class="chart-title"> Historial de Ruido — Hoy</div>
        <div class="chart-sub">Actualización automática cada 5 segundos</div>
        <canvas id="chartLive" height="80"></canvas>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const liveChart = new Chart(document.getElementById('chartLive'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Decibeles (dB)',
                    data: [],
                    borderColor: '#00ACC1',
                    backgroundColor: 'rgba(0,172,193,.1)',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: 0.4,
                    fill: true,
                }, {
                    label: 'Límite 85 dB',
                    data: [],
                    borderColor: '#E53935',
                    borderDash: [5, 4],
                    borderWidth: 1.5,
                    pointRadius: 0,
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 400
                },
                scales: {
                    y: {
                        min: 40,
                        max: 110,
                        title: {
                            display: true,
                            text: 'dB'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hora'
                        }
                    }
                }
            }
        });

        function actualizarDatos() {
            fetch('{{ route('monitoreo.datos') }}')
                .then(r => r.json())
                .then(data => {
                    // Actualizar tarjetas
                    data.sensores.forEach(s => {
                        const dbEl = document.getElementById('db-' + s.id);
                        const iconEl = document.getElementById('icon-' + s.id);
                        if (dbEl) {
                            dbEl.innerHTML = s.decibeles + ' <small style="font-size:.9rem">dB</small>';
                            dbEl.className = 'metric-value db-value ' + (s.decibeles >= 85 ? 'db-critical' : s
                                .decibeles >= 70 ? 'db-warning' : 'db-normal');
                        }
                        if (iconEl) {
                            iconEl.className = 'metric-icon ' + (s.critico ? 'bg-red' : 'bg-teal');
                        }
                    });

                    // Actualizar gráfico
                    const labels = data.historial.map(h => h.hora);
                    const values = data.historial.map(h => h.db);
                    liveChart.data.labels = labels;
                    liveChart.data.datasets[0].data = values;
                    liveChart.data.datasets[1].data = Array(labels.length).fill(85);
                    liveChart.update();
                });
        }

        actualizarDatos();
        setInterval(actualizarDatos, 5000);
    </script>
@endpush
