<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Medidor — {{ $trabajador->obra->nombre ?? 'SoundGuard' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body{background:#0d1117;color:#e6edf3;font-family:'Segoe UI',sans-serif;min-height:100vh}
        .header-bar{background:#161b22;border-bottom:1px solid #30363d;padding:12px 16px}
        .obra-badge{background:#1f6feb22;border:1px solid #1f6feb;color:#58a6ff;border-radius:20px;padding:3px 12px;font-size:.8rem}
        .meter-wrap{position:relative;width:230px;height:230px;margin:0 auto}
        .meter-wrap svg{transform:rotate(-90deg)}
        #arcBg{stroke:#21262d}
        #arcFill{stroke:#1f6feb;stroke-linecap:round;transition:stroke-dashoffset .05s linear,stroke .05s linear}
        #arcPeak{stroke:#f85149;stroke-linecap:round;opacity:.85;transition:stroke-dashoffset .4s ease}
        .db-value{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none}
        .db-num{font-size:3.2rem;font-weight:700;line-height:1;transition:color .1s}
        .db-unit{font-size:.85rem;color:#8b949e}
        .db-peak{font-size:.75rem;color:#f85149;margin-top:2px}
        .status-pill{border-radius:20px;padding:4px 16px;font-size:.8rem;font-weight:600;display:inline-block;transition:all .15s}
        .safe{background:#1a3a2a;color:#3fb950;border:1px solid #3fb950}
        .warn{background:#3a2a1a;color:#f0883e;border:1px solid #f0883e}
        .danger{background:#3a1a1a;color:#f85149;border:1px solid #f85149;animation:blink .6s infinite}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.5}}
        .btn-mic{width:72px;height:72px;border-radius:50%;border:none;font-size:2rem;display:flex;align-items:center;justify-content:center;transition:all .2s}
        .btn-mic.off{background:#21262d;color:#8b949e}
        .btn-mic.on{background:#1f6feb;color:#fff;box-shadow:0 0 20px #1f6feb88;animation:pulse 1.5s infinite}
        @keyframes pulse{0%,100%{box-shadow:0 0 20px #1f6feb88}50%{box-shadow:0 0 35px #1f6febcc}}
        .card-dark{background:#161b22;border:1px solid #30363d;border-radius:12px}
        .form-control-dark{background:#0d1117;border:1px solid #30363d;color:#e6edf3;border-radius:8px}
        .form-control-dark:focus{background:#0d1117;border-color:#1f6feb;color:#e6edf3;box-shadow:0 0 0 3px #1f6feb33}
        .calibrate-btn{background:#21262d;border:1px solid #30363d;color:#8b949e;border-radius:8px;padding:6px 14px;font-size:.8rem;cursor:pointer;transition:all .2s}
        .calibrate-btn:hover{border-color:#1f6feb;color:#58a6ff}
        .toast-alert{position:fixed;top:16px;left:50%;transform:translateX(-50%);background:#f85149;color:#fff;padding:12px 20px;border-radius:10px;font-weight:600;z-index:9999;display:none;max-width:90vw;text-align:center}
        .save-indicator{position:fixed;bottom:16px;right:16px;background:#3fb95022;border:1px solid #3fb950;color:#3fb950;border-radius:8px;padding:6px 12px;font-size:.75rem;display:none;z-index:9998}
        .bg-badge{position:fixed;top:16px;right:16px;background:#1f6feb;color:#fff;border-radius:8px;padding:4px 10px;font-size:.72rem;display:none;z-index:9997}
        #registerPanel{display:none}
        #measurePanel{display:none}
    </style>
</head>
<body>

<div class="toast-alert" id="toastAlert">
    <span class="material-icons align-middle me-1" style="font-size:18px">warning</span>
    <span id="toastMsg">¡Nivel peligroso!</span>
</div>
<div class="save-indicator" id="saveIndicator">
    <span class="material-icons align-middle me-1" style="font-size:13px">cloud_done</span>Guardado
</div>
<div class="bg-badge" id="bgBadge">
    <span class="material-icons align-middle me-1" style="font-size:12px">mic</span>Midiendo en 2° plano
</div>

<!-- Header -->
<div class="header-bar d-flex align-items-center justify-content-between">
    <div>
        <div class="fw-bold" style="font-size:.95rem">SoundGuard</div>
        <span class="obra-badge">{{ $trabajador->obra->nombre ?? 'Sin obra' }}</span>
    </div>
    <div id="workerName" class="text-end" style="font-size:.85rem;color:#8b949e">
        @if($trabajador->nombre !== 'Pendiente'){{ $trabajador->nombre }}@endif
    </div>
</div>

<!-- Panel registro -->
<div class="container-fluid px-3 py-3" id="registerPanel">
    <div class="card-dark p-3 mb-3">
        <div class="fw-semibold mb-3" style="color:#58a6ff">
            <span class="material-icons align-middle me-1" style="font-size:18px">person_add</span>Identificación
        </div>
        <div class="mb-2">
            <label class="form-label small text-secondary">Nombre completo *</label>
            <input type="text" id="inputNombre" class="form-control form-control-dark" placeholder="Tu nombre">
        </div>
        <div class="mb-2">
            <label class="form-label small text-secondary">Teléfono (para alertas SMS)</label>
            <input type="tel" id="inputTelefono" class="form-control form-control-dark" placeholder="+56 9 XXXX XXXX">
        </div>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label small text-secondary">Inicio jornada</label>
                <input type="time" id="inputInicio" class="form-control form-control-dark" value="{{ $trabajador->jornada_inicio ?? '08:00' }}">
            </div>
            <div class="col-6">
                <label class="form-label small text-secondary">Fin jornada</label>
                <input type="time" id="inputFin" class="form-control form-control-dark" value="{{ $trabajador->jornada_fin ?? '17:00' }}">
            </div>
        </div>
        <button class="btn btn-primary w-100" onclick="registrar()">
            <span class="material-icons align-middle me-1" style="font-size:18px">check</span>Confirmar e iniciar
        </button>
    </div>
</div>

<!-- Panel medición -->
<div class="container-fluid px-3 py-3" id="measurePanel">

    <!-- Medidor circular -->
    <div class="text-center mb-3">
        <div class="meter-wrap mb-2">
            <svg width="230" height="230" viewBox="0 0 230 230">
                <circle id="arcBg"   cx="115" cy="115" r="100" fill="none" stroke-width="14"/>
                <circle id="arcFill" cx="115" cy="115" r="100" fill="none" stroke-width="14" stroke-dasharray="628" stroke-dashoffset="628"/>
                <circle id="arcPeak" cx="115" cy="115" r="100" fill="none" stroke-width="4"  stroke-dasharray="4 624" stroke-dashoffset="628"/>
            </svg>
            <div class="db-value">
                <div class="db-num" id="dbDisplay">--</div>
                <div class="db-unit">dBA</div>
                <div class="db-peak" id="dbPeak"></div>
            </div>
        </div>
        <div id="statusPill" class="status-pill safe">Esperando...</div>
    </div>

    <!-- Botón mic + calibración -->
    <div class="d-flex align-items-center justify-content-center gap-4 mb-3">
        <div class="text-center">
            <div class="small text-secondary mb-1">Calibrar <span id="calLabel" style="color:#58a6ff">±0</span></div>
            <div class="d-flex gap-1">
                <button class="calibrate-btn" onclick="calibrar(-3)">−3</button>
                <button class="calibrate-btn" onclick="calibrar(-1)">−1</button>
                <button class="calibrate-btn" onclick="calibrar(+1)">+1</button>
                <button class="calibrate-btn" onclick="calibrar(+3)">+3</button>
            </div>
        </div>
        <button class="btn-mic off" id="btnMic" onclick="toggleMic()">
            <span class="material-icons" id="micIcon">mic_off</span>
        </button>
    </div>

    <!-- Info jornada -->
    <div class="card-dark p-3 mb-3">
        <div class="row text-center g-0">
            <div class="col-4 border-end" style="border-color:#30363d!important">
                <div class="small text-secondary">Jornada</div>
                <div class="fw-semibold" id="jornadaInfo" style="font-size:.82rem">--:-- / --:--</div>
            </div>
            <div class="col-4 border-end" style="border-color:#30363d!important">
                <div class="small text-secondary">Tiempo exp.</div>
                <div class="fw-semibold" id="tiempoExp" style="font-size:.82rem">0 min</div>
            </div>
            <div class="col-4">
                <div class="small text-secondary">Límite</div>
                <div class="fw-semibold" style="font-size:.82rem;color:#f0883e">{{ $trabajador->obra->limite_db ?? 85 }} dB</div>
            </div>
        </div>
    </div>

    <!-- Historial -->
    <div class="card-dark p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-semibold" style="color:#58a6ff">Historial (auto-guardado cada 5s)</span>
            <span class="small text-muted" id="nextSave">--</span>
        </div>
        <div id="historial" style="max-height:150px;overflow-y:auto;font-size:.8rem;color:#8b949e">
            <div class="text-center py-2">Sin datos aún</div>
        </div>
    </div>

    <!-- Editar datos -->
    <div class="card-dark p-3">
        <div class="fw-semibold small mb-2" style="color:#8b949e">
            <span class="material-icons align-middle me-1" style="font-size:15px">edit</span>Actualizar datos
        </div>
        <div class="row g-2">
            <div class="col-6"><input type="time" id="editInicio" class="form-control form-control-dark form-control-sm"></div>
            <div class="col-6"><input type="time" id="editFin"    class="form-control form-control-dark form-control-sm"></div>
            <div class="col-12"><input type="tel" id="editTelefono" class="form-control form-control-dark form-control-sm" placeholder="Teléfono"></div>
            <div class="col-12"><button class="btn btn-sm btn-outline-secondary w-100" onclick="actualizarDatos()">Actualizar</button></div>
        </div>
    </div>
</div>

<script>
const TOKEN  = '{{ $token }}';
const LIMITE = {{ $trabajador->obra->limite_db ?? 85 }};
const CSRF   = document.querySelector('meta[name=csrf-token]').content;
const NOMBRE_INICIAL = '{{ $trabajador->nombre }}';
const CIRC   = 2 * Math.PI * 100;
const DB_MIN = 30, DB_MAX = 120;
const DBFS_OFFSET = 85;
const DECAY_COEF  = 0.90;
const PEAK_HOLD_MS = 2500;

let calibracion = 0;
let audioCtx, workletNode, micStream;
let midiendo = false;
let sesionInicio = null;
let dbSmooth = DB_MIN, dbPeak = DB_MIN;
let peakTimer = null;
let ventana5s = [], ventanaInicio = null;
let historialData = [];
let countdown = 5, countdownTimer;
let notifPermiso = false;
let ultimaAlertaTs = 0; // evitar spam de notificaciones

// ── Init ──
window.addEventListener('DOMContentLoaded', () => {
    solicitarNotificaciones();
    if (NOMBRE_INICIAL === 'Pendiente' || NOMBRE_INICIAL === '') {
        document.getElementById('registerPanel').style.display = 'block';
    } else {
        mostrarMedidor();
    }
});

// ── Notificaciones del navegador ──
function solicitarNotificaciones() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(p => { notifPermiso = p === 'granted'; });
    } else if (Notification.permission === 'granted') {
        notifPermiso = true;
    }
}

function enviarNotificacion(titulo, cuerpo) {
    const ahora = Date.now();
    if (ahora - ultimaAlertaTs < 15000) return; // máx 1 notif cada 15s
    ultimaAlertaTs = ahora;

    // Notificación del navegador (funciona en segundo plano en móvil si se agregó a pantalla inicio)
    if (notifPermiso) {
        new Notification(titulo, { body: cuerpo, icon: '/favicon.ico', tag: 'alerta-ruido', renotify: true });
    }
    // Vibración (móvil)
    if ('vibrate' in navigator) navigator.vibrate([300, 100, 300, 100, 300]);
}

function mostrarMedidor() {
    document.getElementById('registerPanel').style.display = 'none';
    document.getElementById('measurePanel').style.display  = 'block';
    const j = '{{ $trabajador->jornada_inicio ?? "" }}';
    const f = '{{ $trabajador->jornada_fin ?? "" }}';
    document.getElementById('jornadaInfo').textContent = (j && f) ? `${j} / ${f}` : 'Sin horario';
    document.getElementById('editInicio').value   = j;
    document.getElementById('editFin').value      = f;
    document.getElementById('editTelefono').value = '{{ $trabajador->telefono ?? "" }}';
}

async function registrar() {
    const nombre = document.getElementById('inputNombre').value.trim();
    if (!nombre) { alert('El nombre es obligatorio'); return; }
    const r = await post(`/trabajador/${TOKEN}/registrar`, {
        nombre,
        telefono:       document.getElementById('inputTelefono').value,
        jornada_inicio: document.getElementById('inputInicio').value,
        jornada_fin:    document.getElementById('inputFin').value,
    });
    if (r.ok) { document.getElementById('workerName').textContent = nombre; mostrarMedidor(); }
}

async function actualizarDatos() {
    const body = {
        nombre:         document.getElementById('workerName').textContent || NOMBRE_INICIAL,
        telefono:       document.getElementById('editTelefono').value,
        jornada_inicio: document.getElementById('editInicio').value,
        jornada_fin:    document.getElementById('editFin').value,
    };
    await post(`/trabajador/${TOKEN}/registrar`, body);
    document.getElementById('jornadaInfo').textContent = `${body.jornada_inicio} / ${body.jornada_fin}`;
}

// ── Micrófono con AudioWorklet (segundo plano real) ──
async function toggleMic() {
    if (!midiendo) await iniciarMic(); else detenerMic();
}

async function iniciarMic() {
    try {
        micStream = await navigator.mediaDevices.getUserMedia({
            audio: { echoCancellation:false, noiseSuppression:false, autoGainControl:false, channelCount:1 },
            video: false
        });

        audioCtx = new (window.AudioContext || window.webkitAudioContext)();

        // Cargar AudioWorklet — corre en hilo separado, no se pausa al minimizar
        await audioCtx.audioWorklet.addModule('/audio-processor.js');
        workletNode = new AudioWorkletNode(audioCtx, 'sound-meter-processor');

        // Recibir RMS desde el worklet
        workletNode.port.onmessage = (e) => procesarRms(e.data.rms);

        const source = audioCtx.createMediaStreamSource(micStream);
        source.connect(workletNode);
        workletNode.connect(audioCtx.destination); // necesario para que el worklet corra

        midiendo      = true;
        sesionInicio  = new Date();
        ventanaInicio = new Date();
        ventana5s     = [];
        dbSmooth      = DB_MIN;
        dbPeak        = DB_MIN;

        document.getElementById('btnMic').className    = 'btn-mic on';
        document.getElementById('micIcon').textContent = 'mic';
        document.getElementById('bgBadge').style.display = 'block';

        iniciarCountdown();
        iniciarUILoop();

    } catch(e) {
        // Fallback a ScriptProcessor si AudioWorklet no está disponible
        if (e.name === 'NotSupportedError' || e.message.includes('worklet')) {
            await iniciarMicFallback();
        } else {
            alert('No se pudo acceder al micrófono: ' + e.message);
        }
    }
}

// Fallback para navegadores sin AudioWorklet
async function iniciarMicFallback() {
    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const analyser = audioCtx.createAnalyser();
    analyser.fftSize = 2048;
    analyser.smoothingTimeConstant = 0;
    audioCtx.createMediaStreamSource(micStream).connect(analyser);

    midiendo = true; sesionInicio = new Date(); ventanaInicio = new Date();
    document.getElementById('btnMic').className    = 'btn-mic on';
    document.getElementById('micIcon').textContent = 'mic';
    iniciarCountdown();

    const buf = new Float32Array(analyser.fftSize);
    function loop() {
        if (!midiendo) return;
        analyser.getFloatTimeDomainData(buf);
        let sum = 0;
        for (let i = 0; i < buf.length; i++) sum += buf[i]*buf[i];
        procesarRms(Math.sqrt(sum / buf.length));
        requestAnimationFrame(loop);
    }
    loop();
}

function detenerMic() {
    midiendo = false;
    clearInterval(countdownTimer);
    clearTimeout(peakTimer);
    workletNode?.disconnect();
    micStream?.getTracks().forEach(t => t.stop());
    audioCtx?.close();
    document.getElementById('btnMic').className    = 'btn-mic off';
    document.getElementById('micIcon').textContent = 'mic_off';
    document.getElementById('dbDisplay').textContent = '--';
    document.getElementById('dbPeak').textContent    = '';
    document.getElementById('statusPill').className  = 'status-pill safe';
    document.getElementById('statusPill').textContent = 'Detenido';
    document.getElementById('nextSave').textContent   = '--';
    document.getElementById('bgBadge').style.display  = 'none';
    actualizarArco(DB_MIN, DB_MIN);
}

// ── Procesar RMS desde worklet ──
function procesarRms(rms) {
    const dbInstant = rms > 1e-6
        ? Math.max(DB_MIN, Math.min(DB_MAX, 20 * Math.log10(rms) + DBFS_OFFSET + calibracion))
        : DB_MIN;

    if (dbInstant >= dbSmooth) dbSmooth = dbInstant;
    else dbSmooth = dbSmooth * DECAY_COEF + dbInstant * (1 - DECAY_COEF);
    dbSmooth = Math.max(DB_MIN, dbSmooth);

    if (dbSmooth > dbPeak) {
        dbPeak = dbSmooth;
        clearTimeout(peakTimer);
        peakTimer = setTimeout(() => { dbPeak = dbSmooth; }, PEAK_HOLD_MS);
    }

    ventana5s.push(dbSmooth);

    // Actualizar tiempo exposición
    if (sesionInicio) {
        const mins = Math.floor((Date.now() - sesionInicio) / 60000);
        document.getElementById('tiempoExp').textContent = `${mins} min`;
    }
}

// Loop de UI separado del worklet (solo visual)
function iniciarUILoop() {
    function uiLoop() {
        if (!midiendo) return;
        actualizarUI(dbSmooth, dbPeak);
        requestAnimationFrame(uiLoop);
    }
    uiLoop();
}

function actualizarUI(db, peak) {
    const dbR = Math.round(db * 10) / 10;
    document.getElementById('dbDisplay').textContent = dbR.toFixed(1);
    document.getElementById('dbPeak').textContent    = peak > db + 1 ? `Pico: ${peak.toFixed(1)} dB` : '';
    document.getElementById('dbDisplay').style.color = db >= LIMITE ? '#f85149' : db >= LIMITE-10 ? '#f0883e' : '#e6edf3';
    actualizarArco(db, peak);

    const pill = document.getElementById('statusPill');
    if (db >= LIMITE) {
        pill.className   = 'status-pill danger';
        pill.textContent = '⚠ PELIGROSO';
    } else if (db >= LIMITE - 10) {
        pill.className   = 'status-pill warn';
        pill.textContent = 'Precaución';
    } else {
        pill.className   = 'status-pill safe';
        pill.textContent = 'Nivel seguro';
    }
}

function actualizarArco(db, peak) {
    const pct    = Math.min(1, Math.max(0, (db - DB_MIN) / (DB_MAX - DB_MIN)));
    const arc    = document.getElementById('arcFill');
    arc.style.strokeDashoffset = CIRC * (1 - pct);
    arc.style.stroke = db >= LIMITE ? '#f85149' : db >= LIMITE-10 ? '#f0883e' : '#1f6feb';
    const pctP   = Math.min(1, Math.max(0, (peak - DB_MIN) / (DB_MAX - DB_MIN)));
    document.getElementById('arcPeak').style.strokeDashoffset = CIRC * (1 - pctP);
}

// ── Auto-guardado cada 5s ──
function iniciarCountdown() {
    countdown = 5;
    clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
        countdown--;
        document.getElementById('nextSave').textContent = `Guardando en ${countdown}s`;
        if (countdown <= 0) { autoGuardar(); countdown = 5; }
    }, 1000);
}

async function autoGuardar() {
    if (!midiendo || ventana5s.length === 0) return;
    const ahora = new Date();
    const fmt   = d => d.toTimeString().slice(0, 8);
    const leq   = calcLeq(ventana5s);
    const body  = { decibeles: Math.round(leq * 10) / 10, hora_inicio: fmt(ventanaInicio), hora_fin: fmt(ahora) };
    ventana5s     = [];
    ventanaInicio = ahora;

    try {
        const r    = await post(`/trabajador/${TOKEN}/medicion`, body);
        const data = await r.json();
        historialData.push({ db: body.decibeles, hora: ahora.toLocaleTimeString(), alerta: data.alerta });
        renderHistorial();

        const ind = document.getElementById('saveIndicator');
        ind.style.display = 'block';
        setTimeout(() => ind.style.display = 'none', 1200);

        if (data.alerta) {
            mostrarToast(`⚠ ${body.decibeles} dB — Supera límite de ${LIMITE} dB`);
            enviarNotificacion('⚠ Alerta de Ruido — SoundGuard',
                `Nivel: ${body.decibeles} dB — Supera el límite de ${LIMITE} dB`);
        }
    } catch(e) { /* silencioso */ }
}

function calcLeq(samples) {
    if (!samples.length) return DB_MIN;
    const sum = samples.reduce((acc, db) => acc + Math.pow(10, db / 10), 0);
    return 10 * Math.log10(sum / samples.length);
}

function calibrar(delta) {
    calibracion += delta;
    document.getElementById('calLabel').textContent = (calibracion >= 0 ? '+' : '') + calibracion;
    mostrarToast(`Calibración: ${calibracion >= 0 ? '+' : ''}${calibracion} dB`, 1200);
}

function renderHistorial() {
    const el = document.getElementById('historial');
    if (!historialData.length) { el.innerHTML = '<div class="text-center py-2">Sin datos aún</div>'; return; }
    el.innerHTML = historialData.slice(-15).reverse().map(d =>
        `<div class="d-flex justify-content-between py-1 border-bottom" style="border-color:#21262d!important">
            <span>${d.hora}</span>
            <span class="${d.db >= LIMITE ? 'text-danger' : d.db >= LIMITE-10 ? 'text-warning' : 'text-success'} fw-semibold">${d.db} dB</span>
        </div>`
    ).join('');
    el.scrollTop = 0;
}

function post(url, body) {
    return fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body:JSON.stringify(body) });
}

let toastTimer;
function mostrarToast(msg, ms = 3000) {
    const t = document.getElementById('toastAlert');
    document.getElementById('toastMsg').textContent = msg;
    t.style.display = 'block';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.style.display = 'none', ms);
}

// Segundo plano: AudioWorklet sigue corriendo, solo pausamos UI
document.addEventListener('visibilitychange', () => {
    const badge = document.getElementById('bgBadge');
    if (document.hidden && midiendo) {
        badge.style.display = 'block'; // worklet sigue activo
    } else {
        badge.style.display = 'none';
        if (midiendo) iniciarUILoop(); // reanudar UI
    }
});
</script>
</body>
</html>
