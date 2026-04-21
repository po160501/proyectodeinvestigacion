@extends('layouts.app')
@section('title', 'Monitoreo en Tiempo Real')
@section('page-title', 'Monitoreo en Tiempo Real')

@section('content')

{{-- ── Tarjetas de obras ── --}}
<div class="row g-3 mb-4" id="obrasGrid">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small">Haz clic en un área para ver sus trabajadores</span>
            <span class="badge bg-secondary" id="badgeActualizacion">Actualizando...</span>
        </div>
    </div>
    {{-- Las tarjetas se renderizan por JS --}}
    <div id="obrasTarjetas" class="row g-3 w-100 mx-0"></div>
</div>

{{-- ── Panel trabajadores + historial ── --}}
<div class="row g-3">

    {{-- Trabajadores de la obra seleccionada --}}
    <div class="col-12 col-lg-5">
        <div class="chart-card h-100">
            <div class="chart-title" id="tituloTrabajadores">
                <span class="material-icons align-middle me-1" style="font-size:18px">group</span>
                Selecciona un área
            </div>
            <div id="listaTrabajadores" style="min-height:120px">
                <p class="text-muted text-center py-4 small">Haz clic en una tarjeta de área</p>
            </div>
        </div>
    </div>

    {{-- Historial ponderado --}}
    <div class="col-12 col-lg-7">
        <div class="chart-card h-100">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-1">
                <div>
                    <div class="chart-title mb-0">Historial de Ruido</div>
                    <div class="chart-sub">Nivel ponderado de todos los trabajadores del área · cada 5 s</div>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Ponderado actual</div>
                    <div id="ponderadoActual" style="font-size:1.6rem;font-weight:700;color:var(--accent)">-- dB</div>
                </div>
            </div>
            <canvas id="chartLive" height="110"></canvas>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let obraSeleccionada = null;
const COLORS = ['#1f6feb','#3fb950','#f0883e','#f85149','#a371f7','#58a6ff'];

// ── Gráfico ──
const liveChart = new Chart(document.getElementById('chartLive'), {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Ponderado (dB)',
            data: [],
            borderColor: '#00ACC1',
            backgroundColor: 'rgba(0,172,193,.12)',
            borderWidth: 2.5,
            pointRadius: 4,
            tension: 0.4,
            fill: true,
        }, {
            label: 'Límite',
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
        animation: { duration: 300 },
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { min: 30, max: 115, title: { display: true, text: 'dB' } },
            x: { title: { display: true, text: 'Hora' }, ticks: { maxTicksLimit: 12 } }
        }
    }
});

// ── Render tarjetas de obras ──
function renderObras(obras) {
    const cont = document.getElementById('obrasTarjetas');
    if (!obras.length) {
        cont.innerHTML = '<div class="col-12"><div class="alert alert-info">No hay obras/áreas registradas.</div></div>';
        return;
    }
    cont.innerHTML = obras.map((o, i) => {
        const db      = o.ponderado ?? '--';
        const critico = o.ponderado !== null && o.ponderado >= o.limite_db;
        const warn    = o.ponderado !== null && o.ponderado >= o.limite_db - 10 && !critico;
        const color   = critico ? '#f85149' : warn ? '#f0883e' : COLORS[i % COLORS.length];
        const activo  = obraSeleccionada === o.id ? 'ring-active' : '';

        return `
        <div class="col-6 col-sm-4 col-xl-3">
            <div class="metric-card obra-card ${activo}" style="cursor:pointer;border:2px solid ${obraSeleccionada===o.id ? color : 'transparent'};transition:border .2s"
                 onclick="seleccionarObra(${o.id}, '${escHtml(o.nombre)}', ${o.limite_db})">
                <div class="metric-icon" style="background:${color}22">
                    <span class="material-icons" style="color:${color}">construction</span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="metric-value" style="color:${color};font-size:1.4rem">
                        ${db}<small style="font-size:.8rem"> dB</small>
                    </div>
                    <div class="metric-label text-truncate">${escHtml(o.nombre)}</div>
                    <div class="metric-sub text-muted">${o.total} trabajador${o.total !== 1 ? 'es' : ''}</div>
                </div>
            </div>
        </div>`;
    }).join('');
}

// ── Render lista de trabajadores ──
function renderTrabajadores(trabajadores, limite) {
    const el = document.getElementById('listaTrabajadores');
    if (!trabajadores.length) {
        el.innerHTML = '<p class="text-muted text-center py-3 small">Sin trabajadores registrados hoy</p>';
        return;
    }
    el.innerHTML = trabajadores.map(t => {
        const db      = t.db ?? '--';
        const critico = t.db !== null && t.db >= limite;
        const warn    = t.db !== null && t.db >= limite - 10 && !critico;
        const cls     = critico ? 'text-danger' : warn ? 'text-warning' : 'text-success';
        const icon    = critico ? 'warning' : warn ? 'error_outline' : 'check_circle';
        const hora    = t.hora ? t.hora.slice(0,5) : '--:--';

        return `
        <div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color:#30363d!important">
            <div class="d-flex align-items-center gap-2">
                <span class="material-icons ${cls}" style="font-size:18px">${icon}</span>
                <div>
                    <div class="fw-semibold" style="font-size:.9rem">${escHtml(t.nombre)}</div>
                    <div class="text-muted" style="font-size:.75rem">Última: ${hora}</div>
                </div>
            </div>
            <div class="${cls} fw-bold" style="font-size:1.1rem">${db !== '--' ? db+' dB' : '--'}</div>
        </div>`;
    }).join('');
}

// ── Actualizar gráfico ──
function actualizarGrafico(historial, limite) {
    const labels = historial.map(h => h.hora);
    const values = historial.map(h => h.db);
    liveChart.data.labels = labels;
    liveChart.data.datasets[0].data = values;
    liveChart.data.datasets[1].data = Array(labels.length).fill(limite ?? 85);

    // Color dinámico según último valor
    const ultimo = values.at(-1);
    const color  = ultimo >= (limite ?? 85) ? '#f85149' : ultimo >= (limite ?? 85) - 10 ? '#f0883e' : '#00ACC1';
    liveChart.data.datasets[0].borderColor = color;
    liveChart.data.datasets[0].backgroundColor = color + '22';
    liveChart.update();

    // Ponderado actual
    const pond = values.length ? values.at(-1) : null;
    const el   = document.getElementById('ponderadoActual');
    el.textContent = pond !== null ? pond + ' dB' : '-- dB';
    el.style.color = pond >= (limite ?? 85) ? '#f85149' : pond >= (limite ?? 85) - 10 ? '#f0883e' : 'var(--accent)';
}

// ── Seleccionar obra ──
let limiteActual = 85;
function seleccionarObra(id, nombre, limite) {
    obraSeleccionada = id;
    limiteActual     = limite;
    document.getElementById('tituloTrabajadores').innerHTML =
        `<span class="material-icons align-middle me-1" style="font-size:18px">group</span>${nombre}`;
}

// ── Fetch principal ──
function actualizarDatos() {
    const url = '{{ route('monitoreo.datos') }}' + (obraSeleccionada ? `?obra_id=${obraSeleccionada}` : '');
    fetch(url)
        .then(r => r.json())
        .then(data => {
            renderObras(data.obras);

            if (obraSeleccionada) {
                renderTrabajadores(data.trabajadores, limiteActual);
                actualizarGrafico(data.historialObra, limiteActual);
            }

            // Badge
            const now = new Date();
            document.getElementById('badgeActualizacion').textContent =
                `Actualizado ${now.toLocaleTimeString()}`;
        });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

actualizarDatos();
setInterval(actualizarDatos, 5000);
</script>
@endpush
