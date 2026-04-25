@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard — Resumen General')

@section('content')

    {{-- ── Tarjetas métricas ── --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-blue"><span class="material-icons">volume_up</span></div>
                <div>
                    <div class="metric-value text-primary" id="valNivelPromedio">{{ number_format($nivelPromedio, 1) }}
                        <small style="font-size:.9rem">dB</small></div>
                    <div class="metric-label">Nivel Promedio de Ruido</div>
                    <div class="metric-sub {{ $nivelPromedio >= 85 ? 'text-danger' : 'text-success' }}">
                        <span class="material-icons"
                            style="font-size:13px;vertical-align:middle">{{ $nivelPromedio >= 85 ? 'warning' : 'check_circle' }}</span>
                        {{ $nivelPromedio >= 85 ? 'Nivel crítico' : 'Nivel seguro' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-red"><span class="material-icons">notifications_active</span></div>
                <div>
                    <div class="metric-value text-danger" id="valAlertasHoy">{{ $alertasHoy }}</div>
                    <div class="metric-label">Alertas Generadas Hoy</div>
                    <div class="metric-sub text-muted">Umbral: 85 dB</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-orange"><span class="material-icons">timer</span></div>
                <div>
                    <div class="metric-value" id="valTiempoPromedio" style="color:var(--warning);font-size:.9rem">
                        {{ number_format($tiempoPromedio, 0) }} <small style="font-size:.9rem">min</small></div>
                    <div class="metric-label" style="line-height:1.2">Tiempo Exposición Promedio</div>
                    <div class="metric-sub {{ $tiempoPromedio > 30 ? 'text-danger' : 'text-success' }}">
                        {{ $tiempoPromedio > 30 ? 'Supera límite recomendado' : 'Dentro del límite' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon bg-teal"><span class="material-icons">smartphone</span></div>
                <div>
                    <div class="metric-value" id="valDispositivosActivos" style="color:var(--accent)">
                        {{ $dispositivosActivos }}</div>
                    <div class="metric-label">Dispositivos Activos</div>
                    <div class="metric-sub text-success">
                        <span class="material-icons" style="font-size:13px;vertical-align:middle">wifi</span> Midiendo hoy
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Fila 1: Ruido por hora ── --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="chart-card">
                <div class="chart-title">Niveles de Ruido por Hora</div>
                <div class="chart-sub">Decibeles registrados hoy — Umbral crítico: 85 dB</div>
                <canvas id="chartRuido" height="70"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Fila 2: PDR ── --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="chart-card">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="chart-title mb-0">PDR — Precisión en Detección de Ruido</div>
                        <div class="chart-sub">Sistema IoT vs equipo patrón (ingreso manual)</div>
                    </div>
                    <div class="text-center px-3 py-2 rounded" style="background:#1f6feb22;border:1px solid #1f6feb">
                        <div class="small text-muted">Precisión promedio</div>
                        <div id="valPdrPromedio" style="font-size:1.8rem;font-weight:700;color:#58a6ff">
                            {{ $pdrPromedio !== null ? $pdrPromedio . '%' : 'Sin datos' }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#modalPdr">
                        <span class="material-icons" style="font-size:16px">add_circle</span> Ver Tabla / Ingresar Datos
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-8"><canvas id="chartPdrLinea" height="110"></canvas></div>
                    <div class="col-12 col-lg-4"><canvas id="chartPdrError" height="110"></canvas></div>
                </div>
                <div class="mt-4">
                    <div class="small fw-semibold mb-2" style="color:#58a6ff">Comparativa de Eventos Críticos (> 85dB)</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Área</th>
                                    <th>iotDB (Sistema)</th>
                                    <th>PATRON (Equipo)</th>
                                    <th>Error (%)</th>
                                    <th>Fuente</th>
                                </tr>
                            </thead>
                            <tbody id="tablePdrBody">
                                @forelse($pdrCombinado->take(10) as $r)
                                    <tr>
                                        <td>{{ $r['hora'] }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $r['area'] }}</span></td>
                                        <td>{{ $r['iot'] ?? '—' }} dB</td>
                                        <td class="{{ $r['patron'] ? '' : 'text-muted fst-italic' }}">
                                            {{ $r['patron'] ? $r['patron'] . ' dB' : 'Pendiente' }}</td>
                                        <td class="{{ ($r['error'] ?? 0) > 5 ? 'text-danger fw-semibold' : 'text-success' }}">
                                            {{ $r['error'] !== null ? $r['error'] . '%' : '—' }}</td>
                                        <td><span class="badge bg-secondary">{{ $r['fuente'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">Sin eventos críticos detectados hoy</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Fila 3: ETAG ── --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="chart-card">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="chart-title mb-0">ETAG — Tiempo de Respuesta de Alertas</div>
                        <div class="chart-sub">Sistema automático + ingreso manual</div>
                    </div>
                    <div class="text-center px-3 py-2 rounded" style="background:#f0883e22;border:1px solid #f0883e">
                        <div class="small text-muted">Tiempo promedio (manual)</div>
                        <div id="valEtagPromedio" style="font-size:1.8rem;font-weight:700;color:#f0883e">
                            {{ $etagPromedio > 0 ? $etagPromedio . 's' : 'Sin datos' }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1"
                        style="color:#f0883e;border-color:#f0883e" data-bs-toggle="modal" data-bs-target="#modalEtag">
                        <span class="material-icons" style="font-size:16px">add_circle</span> Ingresar Evento Manual
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-6"><canvas id="chartEtagBarras" height="130"></canvas></div>
                    <div class="col-12 col-lg-6"><canvas id="chartEtagLinea" height="130"></canvas></div>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-12 col-xl-6">
                        <div class="small fw-semibold mb-2" style="color:#58a6ff">Alertas Automáticas (Sistema)</div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Hora evento</th>
                                        <th>Hora alerta</th>
                                        <th>Respuesta</th>
                                    </tr>
                                </thead>
                                <tbody id="tableEtagSistemaBody">
                                    @forelse($etagData->where('fuente', 'sistema')->take(5) as $e)
                                        <tr class="{{ $e['alto'] ? 'table-danger' : '' }}">
                                            <td>{{ $e['hora_evento'] }}</td>
                                            <td>{{ $e['hora_alerta'] }}</td>
                                            <td class="{{ $e['alto'] ? 'text-danger fw-bold' : 'text-success' }}">
                                                {{ $e['segundos'] }}s
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted text-center">Sin alertas de sistema</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="small fw-semibold mb-2" style="color:#f0883e">Alertas Manuales</div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Hora evento</th>
                                        <th>Hora alerta</th>
                                        <th>Respuesta</th>
                                        <th>Fuente</th>
                                    </tr>
                                </thead>
                                <tbody id="tableEtagManualBody">
                                    @forelse($etagData->where('fuente', 'manual')->take(5) as $e)
                                        <tr>
                                            <td>{{ $e['hora_evento'] }}</td>
                                            <td>{{ $e['hora_alerta'] }}</td>
                                            <td>
                                                {{ $e['segundos'] }}s
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $e['fuente'] }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">Sin alertas manuales</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Fila 4: TERC ── --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="chart-card">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="chart-title mb-0">TERC — Tiempo de Exposición a Ruido Crítico</div>
                        <div class="chart-sub">Sistema automático (≥85 dB) + ingreso manual</div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="text-center px-3 py-2 rounded" style="background:#f8514922;border:1px solid #f85149">
                            <div class="small text-muted">Sistema</div>
                            <div style="font-size:1.4rem;font-weight:700;color:#f85149">
                                {{ $tercPromedio > 0 ? $tercPromedio . ' min' : 'Sin datos' }}</div>
                        </div>
                        <div class="text-center px-3 py-2 rounded" style="background:#f0883e22;border:1px solid #f0883e">
                            <div class="small text-muted">Manual</div>
                            <div style="font-size:1.4rem;font-weight:700;color:#f0883e">
                                {{ $tercPromedioManual > 0 ? $tercPromedioManual . ' min' : 'Sin datos' }}</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#modalTerc">
                        <span class="material-icons" style="font-size:16px">add_circle</span> Ingresar Información Manual
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-6"><canvas id="chartTercDiario" height="130"></canvas></div>
                    <div class="col-12 col-lg-6"><canvas id="chartTercHisto" height="130"></canvas></div>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-12 col-xl-6">
                        <div class="small fw-semibold mb-2" style="color:#f85149">Exposiciones del Sistema (≥85 dB)</div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Minutos</th>
                                        <th>Prom. dB</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tercSistema->take(5) as $t)
                                        <tr>
                                            <td>{{ $t['hora_inicio'] }}</td>
                                            <td>{{ $t['hora_fin'] }}</td>
                                            <td class="text-danger fw-bold">{{ $t['minutos'] }}m</td>
                                            <td>{{ $t['db'] }} dB</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">Sin exposiciones hoy</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="small fw-semibold mb-2" style="color:#f0883e">Exposiciones Manuales</div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Minutos</th>
                                        <th>Fuente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tercManual->take(5) as $t)
                                        <tr>
                                            <td>{{ $t['hora_inicio'] }}</td>
                                            <td>{{ $t['hora_fin'] }}</td>
                                            <td>{{ $t['minutos'] }}m</td>
                                            <td><span class="badge bg-secondary">{{ $t['fuente'] }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">Sin registros manuales</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Fila 5: Comparativo antes/después ── --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="chart-card">
                <div class="chart-title">Impacto del Sistema IoT — Antes vs Después</div>
                <div class="chart-sub">Evaluación comparativa de indicadores PDR · ETAG · TERC</div>

                {{-- KPIs de mejora --}}
                <div class="row g-3 mb-4 mt-1">
                    <div class="col-12 col-md-4">
                        <div class="text-center p-3 rounded" style="background:#3fb95015;border:1px solid #3fb950">
                            <div class="small text-muted mb-1">Mejora PDR</div>
                            <div id="valMejoraPdr" style="font-size:2rem;font-weight:700;color:#3fb950">+{{ $mejoraPdr }}%
                            </div>
                            <div class="small text-muted">Incremento de precisión</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-center p-3 rounded" style="background:#58a6ff15;border:1px solid #58a6ff">
                            <div class="small text-muted mb-1">Mejora ETAG</div>
                            <div id="valMejoraEtag" style="font-size:2rem;font-weight:700;color:#58a6ff">
                                {{ $mejoraEtag > 0 ? '-' : '+' }}{{ abs($mejoraEtag) }}%</div>
                            <div class="small text-muted">Reducción tiempo de alerta</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-center p-3 rounded" style="background:#f0883e15;border:1px solid #f0883e">
                            <div class="small text-muted mb-1">Mejora TERC</div>
                            <div id="valMejoraTerc" style="font-size:2rem;font-weight:700;color:#f0883e">
                                {{ $mejoraTerc > 0 ? '-' : '+' }}{{ abs($mejoraTerc) }}%</div>
                            <div class="small text-muted">Reducción tiempo exposición</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-4"><canvas id="chartCmpPdr" height="180"></canvas></div>
                    <div class="col-12 col-lg-4"><canvas id="chartCmpEtag" height="180"></canvas></div>
                    <div class="col-12 col-lg-4"><canvas id="chartCmpTerc" height="180"></canvas></div>
                </div>

                {{-- Conclusiones automáticas --}}
                <div class="mt-4 p-3 rounded" style="background:#161b22;border:1px solid #30363d">
                    <div class="fw-semibold mb-2" style="color:#58a6ff">
                        <span class="material-icons align-middle me-1"
                            style="font-size:16px">auto_awesome</span>Conclusiones del sistema
                    </div>
                    <ul class="mb-0 small" id="conclusionesList" style="color:#8b949e;line-height:1.9">
                        <li>La precisión del sistema IoT mejoró <strong style="color:#3fb950">{{ $mejoraPdr }}%</strong>
                            respecto al método anterior ({{ $antesPdr }}% → {{ $despuesPdr }}%).</li>
                        <li>El tiempo de respuesta ante alertas
                            @if($mejoraEtag > 0) se redujo un <strong style="color:#58a6ff">{{ $mejoraEtag }}%</strong>
                                ({{ $antesEtag }}s → {{ $despuesEtag }}s).
                            @else aumentó un <strong style="color:#f85149">{{ abs($mejoraEtag) }}%</strong> — requiere
                                optimización.
                            @endif
                        </li>
                        <li>El tiempo de exposición a ruido crítico
                            @if($mejoraTerc > 0) disminuyó un <strong style="color:#f0883e">{{ $mejoraTerc }}%</strong>
                                ({{ $antesExp }} → {{ $despuesExp }} min promedio).
                            @else se mantiene elevado — se recomienda reforzar controles.
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Obras sobre límite ── --}}
    @if($obrasSobreLimite->count())
        <div class="row g-3">
            <div class="col-12">
                <div class="chart-card">
                    <div class="chart-title">
                        <span class="material-icons align-middle me-1" style="font-size:18px;color:#f85149">warning</span>
                        Obras / Áreas con exposición sobre límite hoy
                    </div>
                    <div class="chart-sub">Minutos acumulados con decibeles ≥ límite configurado</div>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Obra / Área</th>
                                    <th>Límite</th>
                                    <th>Prom. dB</th>
                                    <th>Min. sobre límite</th>
                                    <th>Min. totales</th>
                                    <th>Trabajadores</th>
                                    <th>Riesgo</th>
                                </tr>
                            </thead>
                            <tbody id="tableObrasBody">
                                @foreach($obrasSobreLimite as $o)
                                    @php
                                        $pct = $o['min_total'] > 0 ? round($o['min_sobre'] / $o['min_total'] * 100) : 0;
                                        $riesgo = $pct >= 50 ? 'danger' : ($pct >= 25 ? 'warning' : 'secondary');
                                        $riesgoLabel = $pct >= 50 ? 'Alto' : ($pct >= 25 ? 'Medio' : 'Bajo');
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $o['obra'] }}</td>
                                        <td>{{ $o['limite_db'] }} dB</td>
                                        <td class="{{ $o['avg_db'] >= $o['limite_db'] ? 'text-danger fw-bold' : '' }}">
                                            {{ $o['avg_db'] }} dB</td>
                                        <td class="text-danger fw-semibold">{{ $o['min_sobre'] }} min</td>
                                        <td class="text-muted">{{ $o['min_total'] }} min</td>
                                        <td>{{ $o['trabajadores'] }}</td>
                                        <td><span class="badge bg-{{ $riesgo }}">{{ $riesgoLabel }} ({{ $pct }}%)</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

{{-- ── MODALES ── --}}

<!-- Modal PDR -->
<div class="modal fade" id="modalPdr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background:#0d1117;border:1px solid #30363d;color:#c9d1d9">
            <div class="modal-header" style="border-bottom:1px solid #30363d">
                <h5 class="modal-title">PDR — Registro de Datos Patrón</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3"
                    style="background:#1f6feb22;border:1px solid #1f6feb55;color:#58a6ff">
                    <span class="material-icons align-middle me-1" style="font-size:16px">info</span>
                    Ingresa los valores del equipo patrón para los ruidos detectados por el sistema IoT.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-dark align-middle">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Área</th>
                                <th>iotDB (Sistema)</th>
                                <th style="width:150px">PATRON (dB)</th>
                                <th>FUENTE</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pdrCombinado as $r)
                                <tr>
                                    <form method="POST" action="{{ route('dashboard.pdr') }}">
                                        @csrf
                                        <input type="hidden" name="fecha" value="{{ date('Y-m-d') }}">
                                        <input type="hidden" name="hora" value="{{ $r['hora'] }}">
                                        <input type="hidden" name="iot_db" value="{{ $r['iot'] }}">
                                        <td>{{ $r['hora'] }}</td>
                                        <td><span class="small text-muted">{{ $r['area'] }}</span></td>
                                        <td class="fw-bold">{{ $r['iot'] }} dB</td>
                                        <td><input type="number" step="0.1" name="patron_db"
                                                class="form-control form-control-sm bg-dark text-white border-secondary"
                                                value="{{ $r['patron'] }}" placeholder="Patrón" required></td>
                                        <td><input type="text" name="fuente"
                                                class="form-control form-control-sm bg-dark text-white border-secondary"
                                                value="{{ $r['fuente'] !== '—' ? $r['fuente'] : '' }}"
                                                placeholder="Opcional"></td>
                                        <td><button class="btn btn-sm btn-primary">Guardar</button></td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal ETAG -->
<div class="modal fade" id="modalEtag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:#0d1117;border:1px solid #30363d;color:#c9d1d9">
            <div class="modal-header" style="border-bottom:1px solid #30363d">
                <h5 class="modal-title">ETAG — Historial y Nuevo Registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('dashboard.etag') }}" class="row g-2 mb-4 p-3 rounded"
                    style="background:#161b22;border:1px solid #30363d">
                    @csrf
                    <div class="col-6 col-md-3"><label class="small">Fecha</label><input type="date" name="fecha"
                            class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required></div>
                    <div class="col-6 col-md-2"><label class="small">Evento</label><input type="time" step="1"
                            name="hora_evento" class="form-control form-control-sm" required></div>
                    <div class="col-6 col-md-2"><label class="small">Alerta</label><input type="time" step="1"
                            name="hora_alerta" class="form-control form-control-sm" required></div>
                    <div class="col-6 col-md-3"><label class="small">Fuente</label><input type="text" name="fuente"
                            class="form-control form-control-sm" placeholder="Opcional"></div>
                    <div class="col-12 col-md-2 d-flex align-items-end"><button
                            class="btn btn-sm btn-primary w-100">Añadir</button></div>
                </form>
                <div class="table-responsive">
                    <table class="table table-sm table-dark">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Alerta</th>
                                <th>Respuesta</th>
                                <th>Fuente</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($etagData->where('fuente', 'manual') as $e)
                                <tr>
                                    <td>{{ $e['hora_evento'] }}</td>
                                    <td>{{ $e['hora_alerta'] }}</td>
                                    <td>
                                        {{ $e['segundos'] }}s
                                    </td>
                                    <td>{{ $e['fuente'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal TERC -->
<div class="modal fade" id="modalTerc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:#0d1117;border:1px solid #30363d;color:#c9d1d9">
            <div class="modal-header" style="border-bottom:1px solid #30363d">
                <h5 class="modal-title">TERC — Historial y Nuevo Registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('dashboard.terc') }}" class="row g-2 mb-4 p-3 rounded"
                    style="background:#161b22;border:1px solid #30363d">
                    @csrf
                    <div class="col-6 col-md-3"><label class="small">Fecha</label><input type="date" name="fecha"
                            class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required></div>
                    <div class="col-6 col-md-2"><label class="small">Inicio</label><input type="time" step="1"
                            name="hora_inicio" class="form-control form-control-sm" required></div>
                    <div class="col-6 col-md-2"><label class="small">Fin</label><input type="time" step="1"
                            name="hora_fin" class="form-control form-control-sm" required></div>
                    <div class="col-6 col-md-2"><label class="small">dB</label><input type="number" step="0.1"
                            name="decibeles" class="form-control form-control-sm" required></div>
                    <div class="col-6 col-md-3"><label class="small">Fuente</label><input type="text" name="fuente"
                            class="form-control form-control-sm" placeholder="Opcional"></div>
                    <div class="col-12 col-md-2 d-flex align-items-end"><button
                            class="btn btn-sm btn-primary w-100">Añadir</button></div>
                </form>
                <div class="table-responsive">
                    <table class="table table-sm table-dark">
                        <thead>
                            <tr>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Minutos</th>
                                <th>dB</th>
                                <th>Fuente</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tercManual as $t)
                                <tr>
                                    <td>{{ $t['hora_inicio'] }}</td>
                                    <td>{{ $t['hora_fin'] }}</td>
                                    <td>{{ $t['minutos'] }}m</td>
                                    <td>{{ $t['db'] }}</td>
                                    <td>{{ $t['fuente'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const LIMIT_DB = 85;

        // ── Datos PHP → JS ──
        const ruidoHoras = @json($ruidoPorHora->pluck('hora'));
        const ruidoValues = @json($ruidoPorHora->pluck('db'));

        const pdrHoras = @json($pdrCombinado->pluck('hora'));
        const pdrIot = @json($pdrCombinado->pluck('iot'));
        const pdrPatron = @json($pdrCombinado->pluck('patron'));
        const pdrError = @json($pdrCombinado->pluck('error'));

        const etagSeg = @json($etagData->pluck('segundos'));
        const etagFuentes = @json($etagData->pluck('fuente'));

        const tercDias = @json($tercDiario->pluck('dia'));
        const tercSis = @json($tercDiario->pluck('sistema'));
        const tercMan = @json($tercDiario->pluck('manual'));
        const tercMins = @json($tercData->pluck('minutos'));

        const antesPdr = {{ $antesPdr }};
        const despuesPdr = {{ $despuesPdr }};
        const antesEtag = {{ $antesEtag }};
        const despuesEtag = {{ $despuesEtag }};
        const antesExp = {{ $antesExp }};
        const despuesExp = {{ $despuesExp }};

        const DEMO_HORAS = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
        const DEMO_RUIDO = [62, 71, 88, 79, 85, 92, 74, 68, 77];
        const DEMO_PDR_IOT = [72, 78, 85, 80, 88, 91, 76, 70, 79];
        const DEMO_PATRON = [70, 76, 83, 82, 86, 89, 74, 72, 77];
        const DEMO_ERROR = [2.9, 2.6, 2.4, 2.4, 2.3, 2.2, 2.7, 2.8, 2.6];

        // ── Gráfico 1: Ruido por hora ──
        new Chart(document.getElementById('chartRuido'), {
            type: 'line',
            data: {
                labels: ruidoHoras.length ? ruidoHoras : DEMO_HORAS,
                datasets: [{
                    label: 'Decibeles (dB)',
                    data: ruidoValues.length ? ruidoValues : DEMO_RUIDO,
                    borderColor: '#1565C0', backgroundColor: 'rgba(21,101,192,.1)',
                    borderWidth: 2.5, pointRadius: 5, tension: 0.4, fill: true,
                    pointBackgroundColor: (ruidoValues.length ? ruidoValues : DEMO_RUIDO).map(v => v >= LIMIT_DB ? '#E53935' : '#1565C0'),
                }, {
                    label: 'Límite 85 dB', data: Array(ruidoHoras.length ? ruidoHoras.length : DEMO_HORAS.length).fill(LIMIT_DB),
                    borderColor: '#E53935', borderDash: [6, 4], borderWidth: 1.5, pointRadius: 0, fill: false,
                }]
            },
            options: {
                responsive: true, plugins: { legend: { position: 'top' } },
                scales: { y: { min: 40, max: 110, title: { display: true, text: 'dB' } } }
            }
        });

        // ── Gráfico PDR línea ──
        new Chart(document.getElementById('chartPdrLinea'), {
            type: 'line',
            data: {
                labels: pdrHoras.length ? pdrHoras : DEMO_HORAS,
                datasets: [{
                    label: 'Sistema IoT (dB)', data: pdrIot.length ? pdrIot : DEMO_PDR_IOT,
                    borderColor: '#1f6feb', backgroundColor: 'rgba(31,111,235,.1)',
                    borderWidth: 2, pointRadius: 4, tension: 0.4, fill: true,
                }, {
                    label: 'Equipo patrón (dB)', data: pdrPatron.length ? pdrPatron : DEMO_PATRON,
                    borderColor: '#3fb950', backgroundColor: 'rgba(63,185,80,.08)',
                    borderWidth: 2, pointRadius: 4, tension: 0.4, fill: true, borderDash: [4, 3],
                }]
            },
            options: {
                responsive: true, plugins: { legend: { position: 'top' } },
                scales: { y: { min: 40, max: 120, title: { display: true, text: 'dB' } } }
            }
        });

        // ── Gráfico PDR error ──
        new Chart(document.getElementById('chartPdrError'), {
            type: 'bar',
            data: {
                labels: pdrHoras.length ? pdrHoras : DEMO_HORAS,
                datasets: [{
                    label: 'Error (%)', data: pdrError.length ? pdrError : DEMO_ERROR,
                    backgroundColor: (pdrError.length ? pdrError : DEMO_ERROR).map(v => v > 5 ? 'rgba(248,81,73,.8)' : 'rgba(63,185,80,.8)'),
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true, plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Error (%)' } } }
            }
        });

        // ── Gráfico ETAG barras ──
        const etagLabels = etagSeg.map((_, i) => 'E' + (i + 1));
        new Chart(document.getElementById('chartEtagBarras'), {
            type: 'bar',
            data: {
                labels: etagLabels,
                datasets: [{
                    label: 'Tiempo respuesta (s)',
                    data: etagSeg,
                    backgroundColor: etagFuentes.map((f, i) => f === 'sistema' ? 'rgba(31,111,235,.8)' : 'rgba(240,136,62,.8)'),
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true, labels: {
                            generateLabels: (chart) => ([
                                { text: 'Sistema', fillStyle: 'rgba(31,111,235,.8)' },
                                { text: 'Manual', fillStyle: 'rgba(240,136,62,.8)' }
                            ])
                        }
                    }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Segundos' } } }
            }
        });

        // ── Gráfico ETAG tendencia ──
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
                responsive: true, plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Segundos' } } }
            }
        });

        // ── Gráfico TERC diario (Barras comparativas: Sistema vs Manual) ──
        new Chart(document.getElementById('chartTercDiario'), {
            type: 'bar',
            data: {
                labels: tercDias,
                datasets: [
                    { label: 'Sistema (min)', data: tercSis, backgroundColor: 'rgba(248,81,73,.8)', borderRadius: 4 },
                    { label: 'Manual (min)', data: tercMan, backgroundColor: 'rgba(240,136,62,.8)', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Minutos' } } }
            }
        });

        // ── Gráfico TERC histograma ──
        const bins = [0, 0, 0, 0, 0]; // <10, 10-20, 20-30, 30-40, >40
        (tercMins.length ? tercMins : [5, 15, 25, 35, 45, 8, 22, 38, 12, 42]).forEach(m => {
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
                responsive: true, plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Frecuencia' } } }
            }
        });

        // ── Comparativos antes/después ──
        const cmpOpts = (titulo) => ({
            responsive: true,
            plugins: { legend: { position: 'top' }, title: { display: true, text: titulo, font: { size: 12 } } },
            scales: { y: { beginAtZero: true } }
        });
        const cmpColors = ['rgba(117,117,117,.75)', 'rgba(31,111,235,.85)'];

        new Chart(document.getElementById('chartCmpPdr'), {
            type: 'bar',
            data: {
                labels: ['Precisión (%)'],
                datasets: [
                    { label: 'Antes', data: [antesPdr], backgroundColor: cmpColors[0], borderRadius: 6 },
                    { label: 'Después', data: [despuesPdr], backgroundColor: cmpColors[1], borderRadius: 6 },
                ]
            },
            options: cmpOpts('PDR — Precisión')
        });

        new Chart(document.getElementById('chartCmpEtag'), {
            type: 'bar',
            data: {
                labels: ['Tiempo alerta (s)'],
                datasets: [
                    { label: 'Antes', data: [antesEtag], backgroundColor: cmpColors[0], borderRadius: 6 },
                    { label: 'Después', data: [despuesEtag], backgroundColor: 'rgba(240,136,62,.85)', borderRadius: 6 },
                ]
            },
            options: cmpOpts('ETAG — Tiempo de Alerta')
        });

        new Chart(document.getElementById('chartCmpTerc'), {
            type: 'bar',
            data: {
                labels: ['Exposición (min)'],
                datasets: [
                    { label: 'Antes', data: [antesExp], backgroundColor: cmpColors[0], borderRadius: 6 },
                    { label: 'Después', data: [despuesExp], backgroundColor: 'rgba(248,81,73,.85)', borderRadius: 6 },
                ]
            },
            options: cmpOpts('TERC — Tiempo Exposición')
        });

        // ── Polling AJAX ──
        const charts = {
            ruido: Chart.getChart('chartRuido'),
            pdrL: Chart.getChart('chartPdrLinea'),
            pdrE: Chart.getChart('chartPdrError'),
            etagB: Chart.getChart('chartEtagBarras'),
            etagL: Chart.getChart('chartEtagLinea'),
            tercD: Chart.getChart('chartTercDiario'),
            tercH: Chart.getChart('chartTercHisto'),
            cmpP: Chart.getChart('chartCmpPdr'),
            cmpE: Chart.getChart('chartCmpEtag'),
            cmpT: Chart.getChart('chartCmpTerc'),
        };

        async function refreshDashboard() {
            try {
                const res = await fetch('{{ route('dashboard.api') }}');
                const d = await res.json();

                // Cards
                document.getElementById('valNivelPromedio').innerText = d.nivelPromedio + ' dB';
                document.getElementById('valAlertasHoy').innerText = d.alertasHoy;
                document.getElementById('valTiempoPromedio').innerText = d.tiempoPromedio + ' min';
                document.getElementById('valDispositivosActivos').innerText = d.dispositivosActivos;
                document.getElementById('valPdrPromedio').innerText = d.pdrPromedio ? d.pdrPromedio + '%' : 'Sin datos';
                document.getElementById('valEtagPromedio').innerText = d.etagPromedio > 0 ? d.etagPromedio + 's' : 'Sin datos';

                // Mejora & Conclusiones
                document.getElementById('valMejoraPdr').innerText = '+' + d.mejoraPdr + '%';
                document.getElementById('valMejoraEtag').innerText = (d.mejoraEtag > 0 ? '-' : '+') + Math.abs(d.mejoraEtag) + '%';
                document.getElementById('valMejoraTerc').innerText = (d.mejoraTerc > 0 ? '-' : '+') + Math.abs(d.mejoraTerc) + '%';

                const cList = document.getElementById('conclusionesList');
                cList.innerHTML = `
                <li>La precisión del sistema IoT mejoró <strong style="color:#3fb950">${d.mejoraPdr}%</strong> respecto al método anterior (${d.antesPdr}% → ${d.despuesPdr}%).</li>
                <li>El tiempo de respuesta ante alertas
                    ${d.mejoraEtag > 0 ? `<strong style="color:#58a6ff">se redujo un ${d.mejoraEtag}%</strong> (${d.antesEtag}s → ${d.despuesEtag}s).` : `<strong style="color:#f85149">aumentó un ${Math.abs(d.mejoraEtag)}%</strong> — requiere optimización.`}
                </li>
                <li>El tiempo de exposición a ruido crítico
                    ${d.mejoraTerc > 0 ? `<strong style="color:#f0883e">disminuyó un ${d.mejoraTerc}%</strong> (${d.antesExp} → ${d.despuesExp} min promedio).` : `<span style="color:#8b949e">se mantiene elevado — se recomienda reforzar controles.</span>`}
                </li>
            `;

                // Charts
                charts.ruido.data.labels = d.ruidoPorHora.map(r => r.hora);
                charts.ruido.data.datasets[0].data = d.ruidoPorHora.map(r => r.db);
                charts.ruido.update('none');

                charts.pdrL.data.labels = d.pdrCombinado.map(p => p.hora);
                charts.pdrL.data.datasets[0].data = d.pdrCombinado.map(p => p.iot);
                charts.pdrL.data.datasets[1].data = d.pdrCombinado.map(p => p.patron);
                charts.pdrL.update('none');

                charts.pdrE.data.labels = d.pdrCombinado.map(p => p.hora);
                charts.pdrE.data.datasets[0].data = d.pdrCombinado.map(p => p.error);
                charts.pdrE.update('none');

                charts.etagB.data.labels = d.etagData.map((_, i) => 'E' + (i + 1));
                charts.etagB.data.datasets[0].data = d.etagData.map(e => e.segundos);
                charts.etagB.update('none');

                charts.etagL.data.labels = d.etagData.map((_, i) => 'E' + (i + 1));
                charts.etagL.data.datasets[0].data = d.etagData.map(e => e.segundos);
                charts.etagL.update('none');

                charts.tercD.data.labels = d.tercDiario.map(t => t.dia);
                charts.tercD.data.datasets[0].data = d.tercDiario.map(t => t.sistema);
                charts.tercD.data.datasets[1].data = d.tercDiario.map(t => t.manual);
                charts.tercD.update('none');

                charts.cmpP.data.datasets[1].data = [d.despuesPdr]; charts.cmpP.update('none');
                charts.cmpE.data.datasets[1].data = [d.despuesEtag]; charts.cmpE.update('none');
                charts.cmpT.data.datasets[1].data = [d.despuesExp]; charts.cmpT.update('none');

                // Tables
                const buildTable = (id, rows, callback) => {
                    const body = document.getElementById(id);
                    if (!rows.length) {
                        body.innerHTML = `<tr><td colspan="10" class="text-muted text-center italic">Sin datos registrados</td></tr>`;
                        return;
                    }
                    body.innerHTML = rows.map(callback).join('');
                };

                buildTable('tablePdrBody', d.pdrCombinado.slice(0, 10), r => `
                <tr>
                    <td>${r.hora}</td>
                    <td>${r.iot || '—'} dB</td>
                    <td class="${r.patron ? '' : 'text-muted fst-italic'}">${r.patron ? r.patron + ' dB' : 'Pendiente'}</td>
                    <td class="${(r.error || 0) > 5 ? 'text-danger fw-semibold' : 'text-success'}">${r.error !== null ? r.error + '%' : '—'}</td>
                    <td><span class="badge bg-secondary">${r.fuente}</span></td>
                </tr>
            `);

                buildTable('tableEtagSistemaBody', d.etagData.filter(e => e.fuente === 'sistema').slice(0, 5), e => `
                <tr class="${e.alto ? 'table-danger' : ''}">
                    <td>${e.hora_evento}</td><td>${e.hora_alerta}</td><td class="${e.alto ? 'text-danger fw-bold' : 'text-success'}">${e.segundos}s</td>
                </tr>
            `);

                buildTable('tableEtagManualBody', d.etagData.filter(e => e.fuente === 'manual').slice(0, 5), e => `
                <tr><td>${e.hora_evento}</td><td>${e.hora_alerta}</td><td>${e.segundos}s</td><td><span class="badge bg-secondary">${e.fuente}</span></td></tr>
            `);

                buildTable('tableObrasBody', d.obrasSobreLimite, o => {
                    const pct = o.min_total > 0 ? Math.round(o.min_sobre / o.min_total * 100) : 0;
                    const rColor = pct >= 50 ? 'danger' : (pct >= 25 ? 'warning' : 'secondary');
                    const rLabel = pct >= 50 ? 'Alto' : (pct >= 25 ? 'Medio' : 'Bajo');
                    return `
                    <tr>
                        <td class="fw-semibold">${o.obra}</td><td>${o.limite_db} dB</td>
                        <td class="${o.avg_db >= o.limite_db ? 'text-danger fw-bold' : ''}">${o.avg_db} dB</td>
                        <td class="text-danger fw-semibold">${o.min_sobre} min</td><td class="text-muted">${o.min_total} min</td>
                        <td>${o.trabajadores}</td><td><span class="badge bg-${rColor}">${rLabel} (${pct}%)</span></td>
                    </tr>
                `;
                });

            } catch (e) { console.error('Error refreshing dashboard:', e); }
        }

        setInterval(refreshDashboard, 10000); // 10s
    </script>
@endpush