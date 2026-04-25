@extends('layouts.app')
@section('title', 'Monitoreo en Tiempo Real')
@section('page-title', 'Monitoreo en Tiempo Real')

@push('styles')
    <style>
        .tree-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s;
            user-select: none;
        }

        .tree-item:hover {
            background: #21262d;
        }

        .tree-item.active {
            background: #1f6feb18;
            border-left: 3px solid #1f6feb;
        }

        .tree-area {
            font-weight: 600;
            font-size: .88rem;
        }

        .tree-trab {
            font-size: .83rem;
            padding-left: 24px;
        }

        .tree-children {
            overflow: hidden;
            transition: max-height .25s ease;
            max-height: 0;
        }

        .tree-children.open {
            max-height: 2000px;
        }

        .chevron {
            font-size: 18px;
            transition: transform .2s;
            color: #8b949e;
            flex-shrink: 0;
        }

        .chevron.open {
            transform: rotate(90deg);
        }

        .db-badge {
            font-size: .72rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            white-space: nowrap;
        }

        .db-safe {
            background: #3fb95022;
            color: #3fb950;
            border: 1px solid #3fb95055;
        }

        .db-warn {
            background: #f0883e22;
            color: #f0883e;
            border: 1px solid #f0883e55;
        }

        .db-danger {
            background: #f8514922;
            color: #f85149;
            border: 1px solid #f8514955;
        }

        .db-none {
            background: #30363d44;
            color: #8b949e;
            border: 1px solid #30363d;
        }

        .tree-scroll {
            max-height: 480px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')

    {{-- ── Cuadros de obras ── --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="small text-muted">Clic en obra para filtrar el árbol</span>
        <span class="badge bg-secondary" id="badgeAct">Actualizando...</span>
    </div>
    <div id="obrasGrid" class="row g-3 mb-4"></div>

    {{-- ── Layout 2 columnas ── --}}
    <div class="row g-3">

        {{-- Izquierda: árbol áreas → trabajadores --}}
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-title mb-3">
                    <span class="material-icons align-middle me-1" style="font-size:18px">account_tree</span>
                    Áreas / Trabajadores
                </div>
                <div class="tree-scroll" id="arbolAreas">
                    <div class="text-muted small text-center py-3">Selecciona una obra</div>
                </div>
            </div>
        </div>

        {{-- Derecha: gráfico --}}
        <div class="col-12 col-lg-8">
            <div class="chart-card">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <div class="chart-title mb-0">Historial de Ruido</div>
                        <div class="chart-sub" id="subtituloHistorial">Selecciona una obra, área o trabajador</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Nivel actual</div>
                        <div id="nivelActual" style="font-size:1.8rem;font-weight:700;color:var(--accent)">-- dB</div>
                    </div>
                </div>
                <canvas id="chartLive" height="120"></canvas>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const URL_DATOS = '{{ route('monitoreo.datos') }}';
        const COLORS = ['#1f6feb', '#3fb950', '#f0883e', '#f85149', '#a371f7', '#58a6ff'];

        let obraActual = null;
        let areaActual = null;
        let trabajadorActual = null;
        let areasAbiertas = new Set();
        let obrasCache = [];
        let trabajadoresCache = {};

        // ── Gráfico ──
        const liveChart = new Chart(document.getElementById('chartLive'), {
            type: 'line',
            data: {
                labels: [], datasets: [
                    {
                        label: 'dB', data: [], borderColor: '#00ACC1', backgroundColor: 'rgba(0,172,193,.12)',
                        borderWidth: 2.5, pointRadius: 4, tension: 0.4, fill: true
                    },
                    {
                        label: 'Límite', data: [], borderColor: '#E53935', borderDash: [5, 4],
                        borderWidth: 1.5, pointRadius: 0, fill: false
                    }
                ]
            },
            options: {
                responsive: true, animation: { duration: 300 },
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { min: 30, max: 115, title: { display: true, text: 'dB' } },
                    x: { ticks: { maxTicksLimit: 14 } }
                }
            }
        });

        // ── Fetch ──
        function fetchDatos() {
            const p = new URLSearchParams();
            if (obraActual) p.set('obra_id', obraActual.id);
            if (trabajadorActual) p.set('trabajador_id', trabajadorActual.id);

            fetch(URL_DATOS + (p.toString() ? '?' + p : ''))
                .then(r => r.json())
                .then(data => {
                    obrasCache = data.obras;
                    if (obraActual && data.trabajadores) {
                        trabajadoresCache[obraActual.id] = data.trabajadores;
                    }
                    renderObras(data.obras);
                    renderArbol();
                    if (data.historial !== undefined) actualizarGrafico(data.historial, data.limite ?? 85, data.titulo);
                    document.getElementById('badgeAct').textContent = 'Actualizado ' + new Date().toLocaleTimeString();
                });
        }

        // ── Render cuadros obras ──
        function renderObras(obras) {
            const grid = document.getElementById('obrasGrid');
            if (!obras.length) {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-info">Sin obras registradas.</div></div>';
                return;
            }
            grid.innerHTML = obras.map((o, i) => {
                const critico = o.ponderado !== null && o.ponderado >= o.limite_db;
                const warn = o.ponderado !== null && o.ponderado >= o.limite_db - 10 && !critico;
                const color = critico ? '#f85149' : warn ? '#f0883e' : COLORS[i % COLORS.length];
                const activa = obraActual?.id === o.id;
                return `
            <div class="col-6 col-sm-4 col-xl-3">
                <div class="metric-card" style="cursor:pointer;border:2px solid ${activa ? color : 'transparent'};transition:border .2s"
                     onclick="seleccionarObra(${o.id},'${esc(o.nombre)}',${o.limite_db})">
                    <div class="metric-icon" style="background:${color}22">
                        <span class="material-icons" style="color:${color}">construction</span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="metric-value" style="color:${color};font-size:1.3rem">
                            ${o.ponderado !== null ? o.ponderado : '--'}<small style="font-size:.75rem"> dB</small>
                        </div>
                        <div class="metric-label text-truncate">${esc(o.nombre)}</div>
                        <div class="metric-sub text-muted">${o.total} trabajador${o.total !== 1 ? 'es' : ''}</div>
                    </div>
                </div>
            </div>`;
            }).join('');
        }

        // ── Seleccionar obra ──
        function seleccionarObra(id, nombre, limite) {
            if (obraActual?.id === id) {
                // Deseleccionar
                obraActual = areaActual = trabajadorActual = null;
                areasAbiertas.clear();
                limpiarGrafico('Selecciona una obra, área o trabajador');
                renderObras(obrasCache);
                document.getElementById('arbolAreas').innerHTML =
                    '<div class="text-muted small text-center py-3">Selecciona una obra</div>';
                return;
            }
            obraActual = { id, nombre, limite };
            areaActual = null;
            trabajadorActual = null;
            areasAbiertas.clear();
            document.getElementById('subtituloHistorial').textContent = `Promedio ponderado — ${nombre}`;
            fetchDatos();
        }

        // ── Render árbol áreas → trabajadores ──
        function renderArbol() {
            if (!obraActual) return;
            const obra = obrasCache.find(o => o.id === obraActual.id);
            if (!obra) return;

            const trabajadores = trabajadoresCache[obraActual.id] ?? [];
            const arbol = document.getElementById('arbolAreas');

            if (!obra.areas.length) {
                arbol.innerHTML = '<div class="text-muted small text-center py-3">Sin áreas registradas</div>';
                return;
            }

            arbol.innerHTML = obra.areas.map(a => {
                const abierta = areasAbiertas.has(a.id);
                const aActiva = areaActual?.id === a.id && !trabajadorActual;
                const trabsArea = trabajadores.filter(t => t.area_id === a.id);

                const filaArea = `
            <div class="tree-item tree-area ${aActiva ? 'active' : ''}"
                 onclick="toggleArea(${a.id},'${esc(a.nombre)}')">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <span class="material-icons chevron ${abierta ? 'open' : ''}" id="chev-${a.id}">chevron_right</span>
                    <span class="material-icons" style="color:#58a6ff;font-size:17px">layers</span>
                    <span class="text-truncate">${esc(a.nombre)}</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="db-badge ${dbClass(a.ponderado, obraActual.limite)}">
                        ${a.ponderado !== null ? a.ponderado + ' dB' : '--'}
                    </span>
                    <span class="text-muted" style="font-size:.72rem">${a.total}</span>
                </div>
            </div>`;

                const filasTrab = trabsArea.length
                    ? trabsArea.map(t => {
                        const tActiva = trabajadorActual?.id === t.id;
                        return `
                    <div class="tree-item tree-trab ${tActiva ? 'active' : ''}"
                         onclick="seleccionarTrabajador(${t.id},'${esc(t.nombre)}',event)">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <span class="material-icons" style="color:${dbColor(t.db, obraActual.limite)};font-size:16px">person</span>
                            <span class="text-truncate">${esc(t.nombre)}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="db-badge ${dbClass(t.db, obraActual.limite)}">
                                ${t.db !== null ? t.db + ' dB' : '--'}
                            </span>
                            <span class="text-muted" style="font-size:.7rem">${t.hora ? t.hora.slice(0, 5) : '--'}</span>
                        </div>
                    </div>`;
                    }).join('')
                    : '<div class="tree-trab text-muted py-1" style="font-size:.78rem;padding-left:36px">Sin trabajadores en esta área</div>';

                return `
            <div class="mb-1">
                ${filaArea}
                <div class="tree-children ${abierta ? 'open' : ''}" id="children-${a.id}">
                    ${filasTrab}
                </div>
            </div>`;
            }).join('');
        }

        // ── Toggle área ──
        function toggleArea(id, nombre) {
            const yaAbierta = areasAbiertas.has(id);
            if (yaAbierta && areaActual?.id === id && !trabajadorActual) {
                areasAbiertas.delete(id);
                areaActual = null;
                // Volver a historial de obra
                document.getElementById('subtituloHistorial').textContent = `Promedio ponderado — ${obraActual.nombre}`;
                trabajadorActual = null;
                fetchDatos();
                return;
            }
            areasAbiertas.add(id);
            areaActual = { id, nombre };
            trabajadorActual = null;
            document.getElementById('subtituloHistorial').textContent = `Promedio área — ${nombre}`;
            // Historial del área = filtrar trabajadores de esa área
            fetchDatos();
        }

        // ── Seleccionar trabajador ──
        function seleccionarTrabajador(id, nombre, event) {
            event.stopPropagation();
            if (trabajadorActual?.id === id) {
                trabajadorActual = null;
                document.getElementById('subtituloHistorial').textContent =
                    areaActual ? `Promedio área — ${areaActual.nombre}` : `Promedio ponderado — ${obraActual.nombre}`;
            } else {
                trabajadorActual = { id, nombre };
                document.getElementById('subtituloHistorial').textContent = `Historial individual — ${nombre}`;
            }
            fetchDatos();
        }

        // ── Actualizar gráfico ──
        function actualizarGrafico(historial, limite, titulo) {
            const labels = historial.map(h => h.hora);
            const values = historial.map(h => h.db);
            liveChart.data.labels = labels;
            liveChart.data.datasets[0].data = values;
            liveChart.data.datasets[1].data = Array(labels.length).fill(limite);
            liveChart.data.datasets[0].label = titulo ?? 'dB';
            const ultimo = values.at(-1) ?? 0;
            const color = ultimo >= limite ? '#f85149' : ultimo >= limite - 10 ? '#f0883e' : '#00ACC1';
            liveChart.data.datasets[0].borderColor = color;
            liveChart.data.datasets[0].backgroundColor = color + '22';
            liveChart.update();
            const el = document.getElementById('nivelActual');
            el.textContent = values.length ? values.at(-1) + ' dB' : '-- dB';
            el.style.color = color;
        }

        function limpiarGrafico(msg) {
            liveChart.data.labels = [];
            liveChart.data.datasets[0].data = [];
            liveChart.data.datasets[1].data = [];
            liveChart.update();
            document.getElementById('nivelActual').textContent = '-- dB';
            document.getElementById('subtituloHistorial').textContent = msg ?? '';
        }

        function dbColor(db, limite) {
            if (db === null || db === undefined) return '#8b949e';
            return db >= limite ? '#f85149' : db >= limite - 10 ? '#f0883e' : '#3fb950';
        }
        function dbClass(db, limite) {
            if (db === null || db === undefined) return 'db-none';
            return db >= limite ? 'db-danger' : db >= limite - 10 ? 'db-warn' : 'db-safe';
        }
        function esc(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        fetchDatos();
        setInterval(fetchDatos, 5000);
    </script>
@endpush