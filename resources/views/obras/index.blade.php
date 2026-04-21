@extends('layouts.app')
@section('title', 'Obras / Áreas')
@section('page-title', 'Obras y Áreas')

@section('content')

<div class="row g-3 mb-4">
    <div class="col-12 col-md-5">
        <div class="chart-card">
            <div class="chart-title">Nueva Obra / Área</div>
            <form method="POST" action="{{ route('obras.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Obra Norte, Área Producción">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" placeholder="Opcional">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Límite dB (alerta)</label>
                    <input type="number" name="limite_db" class="form-control" value="85" min="50" max="140">
                </div>
                <button class="btn btn-primary w-100">
                    <span class="material-icons align-middle me-1" style="font-size:18px">add</span>Crear
                </button>
            </form>
        </div>
    </div>

    <div class="col-12 col-md-7">
        <div class="chart-card">
            <div class="chart-title">Obras / Áreas registradas</div>
            @forelse($obras as $obra)
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div>
                    <div class="fw-semibold">{{ $obra->nombre }}</div>
                    <small class="text-muted">{{ $obra->trabajadores_count }} trabajadores · Límite: {{ $obra->limite_db }} dB</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="generarLink({{ $obra->id }}, '{{ addslashes($obra->nombre) }}')">
                        <span class="material-icons" style="font-size:16px">link</span> Link
                    </button>
                    <form method="POST" action="{{ route('obras.destroy', $obra) }}" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">
                            <span class="material-icons" style="font-size:16px">delete</span>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-muted text-center py-3">Sin obras registradas</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Trabajadores por obra -->
@foreach($obras as $obra)
@if($obra->trabajadores_count > 0)
<div class="chart-card mb-3">
    <div class="chart-title">{{ $obra->nombre }} — Trabajadores</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Nombre</th><th>Teléfono</th><th>Jornada</th><th>Link</th></tr></thead>
            <tbody>
            @foreach($obra->trabajadores as $t)
            <tr>
                <td>{{ $t->nombre !== 'Pendiente' ? $t->nombre : '<em class="text-muted">Sin registrar</em>' }}</td>
                <td>{{ $t->telefono ?? '—' }}</td>
                <td>{{ $t->jornada_inicio ? $t->jornada_inicio.' - '.$t->jornada_fin : '—' }}</td>
                <td>
                    @if($t->token_sesion)
                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px"
                        onclick="copiarLink('{{ route('trabajador.medidor', $t->token_sesion) }}')">
                        <span class="material-icons" style="font-size:13px">content_copy</span>
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

<!-- Modal link generado -->
<div class="modal fade" id="modalLink" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Link para trabajador — <span id="obraModalNombre"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Comparte este link con el trabajador. Al abrirlo, ingresará su nombre y podrá medir decibeles.</p>
                <div class="input-group">
                    <input type="text" id="linkGenerado" class="form-control form-control-sm" readonly>
                    <button class="btn btn-outline-secondary btn-sm" onclick="copiarLink(document.getElementById('linkGenerado').value)">
                        <span class="material-icons" style="font-size:16px">content_copy</span>
                    </button>
                </div>
                <div class="mt-2 text-center">
                    <canvas id="qrCanvas" width="180" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
async function generarLink(obraId, obraNombre) {
    const r = await fetch(`/obras/${obraId}/token`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    });
    const data = await r.json();
    document.getElementById('linkGenerado').value = data.url;
    document.getElementById('obraModalNombre').textContent = obraNombre;

    // QR
    const canvas = document.getElementById('qrCanvas');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, 180, 180);
    new QRCode(canvas, { text: data.url, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });

    new bootstrap.Modal(document.getElementById('modalLink')).show();
    setTimeout(() => location.reload(), 500);
}

function copiarLink(url) {
    navigator.clipboard.writeText(url).then(() => alert('Link copiado al portapapeles'));
}
</script>
@endpush
