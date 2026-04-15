@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard — Resumen General')

@section('content')

    {{-- ── Tarjetas métricas ── --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-blue">
                    <span class="material-icons">volume_up</span>
                </div>
                <div>
                    <div class="metric-value text-primary">{{ number_format($nivelPromedio, 1) }} <small
                            style="font-size:.9rem">dB</small></div>
                    <div class="metric-label">Nivel Promedio de Ruido</div>
                    <div class="metric-sub {{ $nivelPromedio >= 85 ? 'text-danger' : 'text-success' }}">
                        <span class="material-icons" style="font-size:13px;vertical-align:middle">
                            {{ $nivelPromedio >= 85 ? 'warning' : 'check_circle' }}
                        </span>
                        {{ $nivelPromedio >= 85 ? 'Nivel crítico' : 'Nivel seguro' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-red">
                    <span class="material-icons">notifications_active</span>
                </div>
                <div>
                    <div class="metric-value text-danger">{{ $alertasHoy }}</div>
                    <div class="metric-label">Alertas Generadas Hoy</div>
                    <div class="metric-sub text-muted">Umbral: 85 dB</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-orange">
                    <span class="material-icons">timer</span>
                </div>
                <div>
                    <div class="metric-value" style="color:var(--warning); font-size: 0.9rem;">{{ number_format($tiempoPromedio, 0) }} <small
                            style="font-size:.9rem">min</small></div>
                    <div class="metric-label" style="line-height:1.2;" >Tiempo Exposición Promedio</div>
                    <div class="metric-sub {{ $tiempoPromedio > 30 ? 'text-danger' : 'text-success' }}">
                        {{ $tiempoPromedio > 30 ? 'Supera límite recomendado' : 'Dentro del límite' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-teal">
                    <span class="material-icons">sensors</span>
                </div>
                <div>
                    <div class="metric-value" style="color:var(--accent)">{{ $sensoresActivos }}</div>
                    <div class="metric-label">Sensores Activos</div>
                    <div class="metric-sub text-success">
                        <span class="material-icons" style="font-size:13px;vertical-align:middle">wifi</span>
                        En línea
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Gráficos fila 1 ── --}}
    <div class="row g-3 mb-3">
        {{-- Gráfico 1: Niveles de ruido por hora --}}
        <div class="col-12 col-lg-8">
            <div class="chart-card">
                <div class="chart-title"> Niveles de Ruido por Hora</div>
                <div class="chart-sub">Decibeles registrados hoy — Umbral crítico: 85 dB</div>
                <canvas id="chartRuido" height="100"></canvas>
            </div>
        </div>
        {{-- Gráfico 2: Alertas por día --}}
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-title"> Alertas por Día</div>
                <div class="chart-sub">Últimos 7 días — Métrica ETAG</div>
                <canvas id="chartAlertas" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Gráficos fila 2 ── --}}
    <div class="row g-3">
        {{-- Gráfico 3: Exposición por trabajador --}}
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <div class="chart-title"> Tiempo de Exposición por Trabajador</div>
                <div class="chart-sub">Minutos de exposición hoy — Métrica TERC</div>
                <canvas id="chartExposicion" height="160"></canvas>
            </div>
        </div>
        {{-- Gráfico 4: Comparación antes vs después --}}
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <div class="chart-title"> Comparación Antes vs Después del Sistema IoT</div>
                <div class="chart-sub">Impacto de la implementación — Métrica PDR</div>
                <canvas id="chartComparacion" height="160"></canvas>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const LIMIT_DB = 85;

        // ── Datos desde PHP ──
        const ruidoHoras = @json($ruidoPorHora->pluck('hora'));
        const ruidoValues = @json($ruidoPorHora->pluck('db'));
        const alertaDias = @json($alertasPorDia->pluck('dia'));
        const alertaVals = @json($alertasPorDia->pluck('total'));
        const expNombres = @json($exposicionPorTrabajador->pluck('nombre'));
        const expMinutos = @json($exposicionPorTrabajador->pluck('minutos'));
        const comp = @json($comparacion);

        // ── Gráfico 1: Línea de ruido ──
        new Chart(document.getElementById('chartRuido'), {
            type: 'line',
            data: {
                labels: ruidoHoras.length ? ruidoHoras : ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00',
                    '14:00', '15:00', '16:00'
                ],
                datasets: [{
                    label: 'Decibeles (dB)',
                    data: ruidoValues.length ? ruidoValues : [62, 71, 88, 79, 85, 92, 74, 68, 77],
                    borderColor: '#1565C0',
                    backgroundColor: 'rgba(21,101,192,.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: ruidoValues.length ?
                        ruidoValues.map(v => v >= LIMIT_DB ? '#E53935' : '#1565C0') :
                        [62, 71, 88, 79, 85, 92, 74, 68, 77].map(v => v >= LIMIT_DB ? '#E53935' :
                        '#1565C0'),
                    pointRadius: 5,
                    tension: 0.4,
                    fill: true,
                }, {
                    label: 'Límite crítico (85 dB)',
                    data: Array(9).fill(LIMIT_DB),
                    borderColor: '#E53935',
                    borderDash: [6, 4],
                    borderWidth: 1.5,
                    pointRadius: 0,
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        min: 40,
                        max: 110,
                        title: {
                            display: true,
                            text: 'Decibeles (dB)'
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

        // ── Gráfico 2: Barras alertas ──
        new Chart(document.getElementById('chartAlertas'), {
            type: 'bar',
            data: {
                labels: alertaDias.length ? alertaDias : ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Alertas',
                    data: alertaVals.length ? alertaVals : [8, 12, 5, 15, 9, 3, 7],
                    backgroundColor: 'rgba(229,57,53,.8)',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'N° Alertas'
                        }
                    }
                }
            }
        });

        // ── Gráfico 3: Barras exposición ──
        new Chart(document.getElementById('chartExposicion'), {
            type: 'bar',
            data: {
                labels: expNombres.length ? expNombres : ['Juan P.', 'María L.', 'Carlos R.', 'Ana T.', 'Luis M.'],
                datasets: [{
                    label: 'Minutos exposición',
                    data: expMinutos.length ? expMinutos : [45, 30, 62, 28, 55],
                    backgroundColor: expMinutos.length ?
                        expMinutos.map(v => v > 30 ? 'rgba(251,140,0,.85)' : 'rgba(67,160,71,.85)') :
                        [45, 30, 62, 28, 55].map(v => v > 30 ? 'rgba(251,140,0,.85)' :
                            'rgba(67,160,71,.85)'),
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Minutos'
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 30
                        }
                    }
                }
            }
        });

        // ── Gráfico 4: Comparación antes/después ──
        new Chart(document.getElementById('chartComparacion'), {
            type: 'bar',
            data: {
                labels: ['Tiempo Exposición (min)', 'Alertas Totales', 'Precisión (%)'],
                datasets: [{
                    label: 'Antes del Sistema IoT',
                    data: [comp.antes.exposicion, comp.antes.alertas, comp.antes.precision],
                    backgroundColor: 'rgba(117,117,117,.75)',
                    borderRadius: 6,
                }, {
                    label: 'Después del Sistema IoT',
                    data: [comp.despues.exposicion, comp.despues.alertas, comp.despues.precision],
                    backgroundColor: 'rgba(21,101,192,.8)',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
