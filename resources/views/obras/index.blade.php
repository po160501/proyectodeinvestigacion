@extends('layouts.app')
@section('title', 'Obras / Áreas')
@section('page-title', 'Obras y Áreas')

@section('content')

<div class="row g-3 mb-4">

    {{-- ── Formulario crear obra ── --}}
    <div class="col-12 col-md-5">
        <div class="chart-card">
            <div class="chart-title">Nueva Obra</div>
            <form method="POST" action="{{ route('obras.store') }}" id="formObra">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Nombre de la obra *</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Obra Norte">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Límite dB (alerta)</label>
                    <input type="number" name="limite_db" class="form-control" value="85" min="50" max="140">
                </div>

                {{-- Áreas dinámicas --}}
                <div class="mb-3">
                    <label class="form-label small">Áreas <span class="text-muted">(mínimo 1)</span></label>
                    <div id="areasContainer">
                        <div class="input-group mb-1 area-row">
                            <input type="text" name="areas[]" class="form-control form-control-sm" placeholder="Nombre del área" required>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarArea(this)" disabled>
                                <span class="material-icons" style="font-size:15px">remove</span>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="agregarArea()">
                        <span class="material-icons align-middle" style="font-size:15px">add</span> Agregar área
                    </button>
                </div>

                <button class="btn btn-primary w-100">
                    <span class="material-icons align-middle me-1" style="font-size:18px">add</span>Crear obra
                </button>
            </form>
        </div>
    </div>

    {{-- ── Lista de obras ── --}}
    <div class="col-12 col-md-7">
        <div class="chart-card">
            <div class="chart-title">Obras registradas</div>
            @forelse($obras as $obra)
            <div class="py-2 border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold">{{ $obra->nombre }}</div>
                        <small class="text-muted">
                            {{ $obra->trabajadores_count }} trabajadores · Límite: {{ $obra->limite_db }} dB
                        </small>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($obra->areas as $area)
                            <span class="badge" style="background:#1f6feb22;color:#58a6ff;border:1px solid #1f6feb44;font-size:.72rem">
                                {{ $area->nombre }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button class="btn btn-sm btn-outline-primary" onclick="generarLink({{ $obra->id }}, '{{ addslashes($obra->nombre) }}')">
                            <span class="material-icons" style="font-size:16px">link</span>
                        </button>
                        <form method="POST" action="{{ route('obras.destroy', $obra) }}" onsubmit="return confirm('¿Eliminar obra y sus áreas?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <span class="material-icons" style="font-size:16px">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-muted text-center py-3">Sin obras registradas</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Tabla trabajadores por obra ── --}}
@foreach($obras as $obra)
@if($obra->trabajadores_count > 0)
<div class="chart-card mb-3">
    <div class="chart-title">{{ $obra->nombre }} — Trabajadores</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Jornada</th>
                    <th>Área</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($obra->trabajadores as $t)
            <tr>
                <td>{!! $t->nombre !== 'Pendiente' ? e($t->nombre) : '<em class="text-muted">Sin registrar</em>' !!}</td>
                <td>{{ $t->telefono ?? '—' }}</td>
                <td>{{ $t->jornada_inicio ? $t->jornada_inicio.' - '.$t->jornada_fin : '—' }}</td>
                <td style="min-width:140px">
                    <select class="form-select form-select-sm"
                            onchange="asignarArea({{ $t->id }}, this.value)">
                        <option value="">— Sin área —</option>
                        @foreach($obra->areas as $area)
                        <option value="{{ $area->id }}" {{ $t->area_id == $area->id ? 'selected' : '' }}>
                            {{ $area->nombre }}
                        </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        @if($t->token_sesion)
                        <button class="btn btn-sm btn-outline-secondary" title="Copiar link"
                            onclick="copiarLink('{{ route('trabajador.medidor', $t->token_sesion) }}')">
                            <span class="material-icons" style="font-size:14px">content_copy</span>
                        </button>
                        @endif
                        <form method="POST" action="{{ route('trabajadores.destroy', $t->id) }}" onsubmit="return confirm('¿Eliminar trabajador?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <span class="material-icons" style="font-size:14px">delete</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

{{-- Modal link --}}
<div class="modal fade" id="modalLink" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Link para trabajador — <span id="obraModalNombre"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Comparte este link con el trabajador.</p>
                <div class="input-group">
                    <input type="text" id="linkGenerado" class="form-control form-control-sm" readonly>
                    <button class="btn btn-outline-secondary btn-sm" onclick="copiarLink(document.getElementById('linkGenerado').value)">
                        <span class="material-icons" style="font-size:16px">content_copy</span>
                    </button>
                </div>
                <div class="mt-3 text-center" id="qrContainer"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// ── Áreas dinámicas ──
function agregarArea() {
    const cont = document.getElementById('areasContainer');
    const div  = document.createElement('div');
    div.className = 'input-group mb-1 area-row';
    div.innerHTML = `
        <input type="text" name="areas[]" class="form-control form-control-sm" placeholder="Nombre del área" required>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarArea(this)">
            <span class="material-icons" style="font-size:15px">remove</span>
        </button>`;
    cont.appendChild(div);
    actualizarBotonesQuitar();
}

function quitarArea(btn) {
    btn.closest('.area-row').remove();
    actualizarBotonesQuitar();
}

function actualizarBotonesQuitar() {
    const rows = document.querySelectorAll('.area-row');
    rows.forEach(r => r.querySelector('button').disabled = rows.length === 1);
}

// ── Asignar área a trabajador (AJAX) ──
async function asignarArea(trabajadorId, areaId) {
    await fetch(`/trabajadores/${trabajadorId}/area`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ area_id: areaId || null })
    });
}

// ── Generar link ──
async function generarLink(obraId, obraNombre) {
    const r    = await fetch(`/obras/${obraId}/token`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await r.json();

    document.getElementById('linkGenerado').value          = data.url;
    document.getElementById('obraModalNombre').textContent = obraNombre;

    const cont = document.getElementById('qrContainer');
    cont.innerHTML = '';
    new QRCode(cont, { text: data.url, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });

    new bootstrap.Modal(document.getElementById('modalLink')).show();
    setTimeout(() => location.reload(), 600);
}

function copiarLink(url) {
    navigator.clipboard.writeText(url).then(() => alert('Link copiado'));
}
</script>
@endpush
