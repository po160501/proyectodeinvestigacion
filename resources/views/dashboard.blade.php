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
                <div class="metric-value text-primary">{{ number_format($nivelPromedio,1) }} <small style="font-size:.9rem">dB</small></div>
                <div class="metric-label">Nivel Promedio de Ruido</div>
                <div class="metric-sub {{ $nivelPromedio >= 85 ? 'text-danger' : 'text-success' }}">
                    <span class="material-icons" style="font-size:13px;vertical-align:middle">{{ $nivelPromedio >= 85 ? 'warning' : 'check_circle' }}</span>
                    {{ $nivelPromedio >= 85 ? 'Nivel crítico' : 'Nivel seguro' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon bg-red"><span class="material-icons">notifications_active</span></div>
            <div>
                <div class="metric-value text-danger">{{ $alertasHoy }}</div>
                <div class="metric-label">Alertas Generadas Hoy</div>
                <div class="metric-sub text-muted">Umbral: 85 dB</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon bg-orange"><span class="material-icons">timer</span></div>
            <div>
                <div class="metric-value" style="color:var(--warning);font-size:.9rem">{{ number_format($tiempoPromedio,0) }} <small style="font-size:.9rem">min</small></div>
                <div class="metric-label" style="line-height:1.2">Tiempo Exposición Promedio</div>
                <div class="metric-sub {{ $tiempoPromedio > 30 ? 'text-danger' : 'text-success' }}">
                    {{ $tiempoPromedio > 30 ? 'Supera límite recomendado' : 'Dentro del límite' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon bg-teal"><span class="material-icons">sensors</span></div>
            <div>
                <div class="metric-value" style="color:var(--accent)">{{ $sensoresActivos }}</div>
                <div class="metric-label">Sensores Activos</div>
                <div class="metric-sub text-success">
                    <span class="material-icons" style="font-size:13px;vertical-align:middle">wifi</span> En línea
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
                    <div class="chart-sub">Comparación sistema IoT vs equipo patrón</div>
                </div>
                <div class="text-center px-3 py-2 rounded" style="background:#1f6feb22;border:1px solid #1f6feb">
                    <div class="small text-muted">Precisión promedio</div>
                    <div style="font-size:1.8rem;font-weight:700;color:#58a6ff">{{ $pdrPromedio }}%</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <canvas id="chartPdrLinea" height="110"></canvas>
                </div>
                <div class="col-12 col-lg-4">
                    <canvas id="chartPdrError" height="110"></canvas>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Hora</th><th>IoT (dB)</th><th>Patrón (dB)</th><th>Error (%)</th></tr></thead>
                    <tbody>
                    @forelse($pdrData->take(8) as $r)
                    <tr>
                        <td>{{ $r['hora'] }}</td>
                        <td>{{ $r['iot'] }}</td>
                        <td>{{ $r['patron'] }}</td>
                        <td class="{{ $r['error'] > 5 ? 'text-danger fw-semibold' : 'text-success' }}">{{ $r['error'] }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-center">Sin mediciones hoy</td></tr>
                    @endforelse
                    </tbody>
                </table>
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
                    <div class="chart-sub">Rapidez del sistema ante niveles de ruido peligrosos</div>
                </div>
                <div class="text-center px-3 py-2 rounded" style="background:#f0883e22;border:1px solid #f0883e">
                    <div class="small text-muted">Tiempo promedio</div>
                    <div style="font-size:1.8rem;font-weight:700;color:#f0883e">{{ $etagPromedio }}s</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <canvas id="chartEtagBarras" height="130"></canvas>
                </div>
                <div class="col-12 col-lg-6">
                    <canvas id="chartEtagLinea" height="130"></canvas>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Hora evento</th><th>Hora alerta</th><th>Tiempo respuesta</th></tr></thead>
                    <tbody>
                    @forelse($etagData->take(8) as $e)
                    <tr class="{{ $e['alto'] ? 'table-danger' : '' }}">
                        <td>{{ $e['hora_evento'] }}</td>
                        <td>{{ $e['hora_alerta'] }}</td>
                        <td class="{{ $e['alto'] ? 'text-danger fw-bold' : 'text-success' }}">
                            {{ $e['segundos'] }}s {{ $e['alto'] ? '⚠' : '' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-muted text-center">Sin alertas hoy</td></tr>
                    @endforelse
                    </tbody>
                </table>
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
                    <div class="chart-sub">Exposición acumulada ≥ 85 dB por trabajadores</div>
                </div>
                <div class="text-center px-3 py-2 rounded" style="background:#f8514922;border:1px solid #f85149">
                    <div class="small text-muted">Exposición promedio</div>
                    <div style="font-size:1.8rem;font-weight:700;color:#f85149">{{ $tercPromedio }} min</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <canvas id="chartTercDiario" height="130"></canvas>
                </div>
                <div class="col-12 col-lg-6">
                    <canvas id="chartTercHisto" height="130"></canvas>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Fecha</th><th>Hora inicio</th><th>Hora fin</th><th>Minutos exp.</th><th>dB</th></tr></thead>
                    <tbody>
                    @forelse($tercData->take(8) as $t)
                    <tr>
                        <td>{{ $t['fecha'] }}</td>
                        <td>{{ $t['hora_inicio'] }}</td>
                        <td>{{ $t['hora_fin'] }}</td>
                        <td class="fw-semibold text-danger">{{ $t['minutos'] }} min</td>
                        <td>{{ $t['db'] }} dB</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted text-center">Sin exposiciones críticas hoy</td></tr>
                    @endforelse
                    </tbody>
                </table>
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
                        <div style="font-size:2rem;font-weight:700;color:#3fb950">+{{ $mejoraPdr }}%</div>
                        <div class="small text-muted">Incremento de precisión</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-center p-3 rounded" style="background:#58a6ff15;border:1px solid #58a6ff">
                        <div class="small text-muted mb-1">Mejora ETAG</div>
                        <div style="font-size:2rem;font-weight:700;color:#58a6ff">{{ $mejoraEtag > 0 ? '-' : '+' }}{{ abs($mejoraEtag) }}%</div>
                        <div class="small text-muted">Reducción tiempo de alerta</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-center p-3 rounded" style="background:#f0883e15;border:1px solid #f0883e">
                        <div class="small text-muted mb-1">Mejora TERC</div>
                        <div style="font-size:2rem;font-weight:700;color:#f0883e">{{ $mejoraTerc > 0 ? '-' : '+' }}{{ abs($mejoraTerc) }}%</div>
                        <div class="small text-muted">Reducción tiempo exposición</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-lg-4"><canvas id="chartCmpPdr"  height="180"></canvas></div>
                <div class="col-12 col-lg-4"><canvas id="chartCmpEtag" height="180"></canvas></div>
                <div class="col-12 col-lg-4"><canvas id="chartCmpTerc" height="180"></canvas></div>
            </div>

            {{-- Conclusiones automáticas --}}
            <div class="mt-4 p-3 rounded" style="background:#161b22;border:1px solid #30363d">
                <div class="fw-semibold mb-2" style="color:#58a6ff">
                    <span class="material-icons align-middle me-1" style="font-size:16px">auto_awesome</span>Conclusiones del sistema
                </div>
                <ul class="mb-0 small" style="color:#8b949e;line-height:1.9">
                    <li>La precisión del sistema IoT mejoró <strong style="color:#3fb950">{{ $mejoraPdr }}%</strong> respecto al método anterior ({{ $antesPdr }}% → {{ $despuesPdr }}%).</li>
                    <li>El tiempo de respuesta ante alertas
                        @if($mejoraEtag > 0) se redujo un <strong style="color:#58a6ff">{{ $mejoraEtag }}%</strong> ({{ $antesEtag }}s → {{ $despuesEtag }}s).
                        @else aumentó un <strong style="color:#f85149">{{ abs($mejoraEtag) }}%</strong> — requiere optimización.
                        @endif
                    </li>
                    <li>El tiempo de exposición a ruido crítico
                        @if($mejoraTerc > 0) disminuyó un <strong style="color:#f0883e">{{ $mejoraTerc }}%</strong> ({{ $antesExp }} → {{ $despuesExp }} min promedio).
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
                    <thead><tr><th>Obra / Área</th><th>Límite</th><th>Prom. dB</th><th>Min. sobre límite</th><th>Min. totales</th><th>Trabajadores</th><th>Riesgo</th></tr></thead>
                    <tbody>
                    @foreach($obrasSobreLimite as $o)
                    @php
                        $pct = $o['min_total'] > 0 ? round($o['min_sobre'] / $o['min_total'] * 100) : 0;
                        $riesgo = $pct >= 50 ? 'danger' : ($pct >= 25 ? 'warning' : 'secondary');
                        $riesgoLabel = $pct >= 50 ? 'Alto' : ($pct >= 25 ? 'Medio' : 'Bajo');
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $o['obra'] }}</td>
                        <td>{{ $o['limite_db'] }} dB</td>
                        <td class="{{ $o['avg_db'] >= $o['limite_db'] ? 'text-danger fw-bold' : '' }}">{{ $o['avg_db'] }} dB</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const LIMIT_DB = 85;

// ── Datos PHP → JS ──
const ruidoHoras  = @json($ruidoPorHora->pluck('hora'));
const ruidoValues = @json($ruidoPorHora->pluck('db'));

const pdrHoras    = @json($pdrData->pluck('hora'));
const pdrIot      = @json($pdrData->pluck('iot'));
const pdrPatron   = @json($pdrData->pluck('patron'));
const pdrError    = @json($pdrData->pluck('error'));

const etagSeg     = @json($etagData->pluck('segundos'));
const etagLabels  = @json($etagData->keys());

const tercDias    = @json($tercDiario->pluck('dia'));
const tercTotales = @json($tercDiario->pluck('total'));
const tercMins    = @json($tercData->pluck('minutos'));

const antesPdr    = {{ $antesPdr }};
const despuesPdr  = {{ $despuesPdr }};
const antesEtag   = {{ $antesEtag }};
const despuesEtag = {{ $despuesEtag }};
const antesExp    = {{ $antesExp }};
const despuesExp  = {{ $despuesExp }};

const DEMO_HORAS   = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00'];
const DEMO_RUIDO   = [62,71,88,79,85,92,74,68,77];
const DEMO_PDR_IOT = [72,78,85,80,88,91,76,70,79];
const DEMO_PATRON  = [70,76,83,82,86,89,74,72,77];
const DEMO_ERROR   = [2.9,2.6,2.4,2.4,2.3,2.2,2.7,2.8,2.6];

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
            label: 'Límite 85 dB', data: Array(9).fill(LIMIT_DB),
            borderColor: '#E53935', borderDash: [6,4], borderWidth: 1.5, pointRadius: 0, fill: false,
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } },
        scales: { y: { min:40, max:110, title:{ display:true, text:'dB' } } } }
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
            borderWidth: 2, pointRadius: 4, tension: 0.4, fill: true, borderDash: [4,3],
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } },
        scales: { y: { min:40, max:120, title:{ display:true, text:'dB' } } } }
});

// ── Gráfico PDR error ──
new Chart(document.getElementById('chartPdrError'), {
    type: 'bar',
    data: {
        labels: pdrHoras.length ? pdrHoras : DEMO_HORAS,
        datasets: [{ label: 'Error (%)', data: pdrError.length ? pdrError : DEMO_ERROR,
            backgroundColor: (pdrError.length ? pdrError : DEMO_ERROR).map(v => v > 5 ? 'rgba(248,81,73,.8)' : 'rgba(63,185,80,.8)'),
            borderRadius: 5 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, title:{ display:true, text:'Error (%)' } } } }
});

// ── Gráfico ETAG barras ──
const etagD = etagSeg.length ? etagSeg : [8,12,5,22,9,31,7,18];
new Chart(document.getElementById('chartEtagBarras'), {
    type: 'bar',
    data: {
        labels: etagD.map((_,i) => 'Evento '+(i+1)),
        datasets: [{ label: 'Tiempo respuesta (s)', data: etagD,
            backgroundColor: etagD.map(v => v > 20 ? 'rgba(248,81,73,.8)' : 'rgba(31,111,235,.8)'),
            borderRadius: 5 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, title:{ display:true, text:'Segundos' } } } }
});

// ── Gráfico ETAG tendencia ──
new Chart(document.getElementById('chartEtagLinea'), {
    type: 'line',
    data: {
        labels: etagD.map((_,i) => 'E'+(i+1)),
        datasets: [{ label: 'Tendencia (s)', data: etagD,
            borderColor: '#f0883e', backgroundColor: 'rgba(240,136,62,.1)',
            borderWidth: 2, pointRadius: 4, tension: 0.4, fill: true }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, title:{ display:true, text:'Segundos' } } } }
});

// ── Gráfico TERC línea diaria ──
new Chart(document.getElementById('chartTercDiario'), {
    type: 'line',
    data: {
        labels: tercDias.length ? tercDias : ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'],
        datasets: [{ label: 'Min. exposición crítica', data: tercTotales.length ? tercTotales : [45,30,62,28,55,40,35],
            borderColor: '#f85149', backgroundColor: 'rgba(248,81,73,.1)',
            borderWidth: 2, pointRadius: 4, tension: 0.4, fill: true }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, title:{ display:true, text:'Minutos' } } } }
});

// ── Gráfico TERC histograma ──
const bins = [0,0,0,0,0]; // <10, 10-20, 20-30, 30-40, >40
(tercMins.length ? tercMins : [5,15,25,35,45,8,22,38,12,42]).forEach(m => {
    if (m < 10) bins[0]++; else if (m < 20) bins[1]++; else if (m < 30) bins[2]++;
    else if (m < 40) bins[3]++; else bins[4]++;
});
new Chart(document.getElementById('chartTercHisto'), {
    type: 'bar',
    data: {
        labels: ['<10 min','10-20','20-30','30-40','>40 min'],
        datasets: [{ label: 'Frecuencia', data: bins,
            backgroundColor: ['rgba(63,185,80,.8)','rgba(63,185,80,.6)','rgba(240,136,62,.7)','rgba(248,81,73,.7)','rgba(248,81,73,.9)'],
            borderRadius: 5 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks:{ stepSize:1 }, title:{ display:true, text:'Frecuencia' } } } }
});

// ── Comparativos antes/después ──
const cmpOpts = (titulo) => ({
    responsive: true,
    plugins: { legend: { position: 'top' }, title: { display: true, text: titulo, font:{ size:12 } } },
    scales: { y: { beginAtZero: true } }
});
const cmpColors = ['rgba(117,117,117,.75)', 'rgba(31,111,235,.85)'];

new Chart(document.getElementById('chartCmpPdr'), {
    type: 'bar',
    data: { labels: ['Precisión (%)'],
        datasets: [
            { label: 'Antes', data: [antesPdr],  backgroundColor: cmpColors[0], borderRadius: 6 },
            { label: 'Después', data: [despuesPdr], backgroundColor: cmpColors[1], borderRadius: 6 },
        ]},
    options: cmpOpts('PDR — Precisión')
});

new Chart(document.getElementById('chartCmpEtag'), {
    type: 'bar',
    data: { labels: ['Tiempo alerta (s)'],
        datasets: [
            { label: 'Antes', data: [antesEtag],  backgroundColor: cmpColors[0], borderRadius: 6 },
            { label: 'Después', data: [despuesEtag], backgroundColor: 'rgba(240,136,62,.85)', borderRadius: 6 },
        ]},
    options: cmpOpts('ETAG — Tiempo de Alerta')
});

new Chart(document.getElementById('chartCmpTerc'), {
    type: 'bar',
    data: { labels: ['Exposición (min)'],
        datasets: [
            { label: 'Antes', data: [antesExp],  backgroundColor: cmpColors[0], borderRadius: 6 },
            { label: 'Después', data: [despuesExp], backgroundColor: 'rgba(248,81,73,.85)', borderRadius: 6 },
        ]},
    options: cmpOpts('TERC — Tiempo Exposición')
});
</script>
@endpush
