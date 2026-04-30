@extends('layouts.app')

@section('title', 'PDR — Precisión en Detección de Ruido')
@section('page-title', 'PDR — Reporte Detallado')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">PDR — Precisión en Detección de Ruido</h1>
            <p class="text-muted small">Comparativa Sistema IoT vs equipo patrón</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2">
            <span class="material-icons" style="font-size:18px">dashboard</span> Volver
        </a>
    </div>

    {{-- Notificación flotante (Toast) --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1060">
        <div id="ajaxToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span class="material-icons">check_circle</span>
                    <span id="ajaxToastMessage">Se registró cambio</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    {{-- Gráficos copiados del Dashboard --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="chart-card">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="chart-title mb-0">Tendencia de Precisión</div>
                        <div class="chart-sub">Decibeles registrados por el sistema vs equipo certificado</div>
                    </div>
                    <div class="text-center px-3 py-2 rounded" style="background:#1f6feb22;border:1px solid #1f6feb">
                        <div class="small text-muted">Precisión promedio</div>
                        <div id="valPdrPromedioHeader" style="font-size:1.5rem;font-weight:700;color:#58a6ff">
                            {{ $pdrPromedio !== null ? $pdrPromedio . '%' : 'Sin datos' }}
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <canvas id="chartPdrLinea" height="150"></canvas>
                    </div>
                    <div class="col-12 col-lg-4">
                        <canvas id="chartPdrError" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Datos --}}
    <div class="table-card">
        <div class="table-header">
            <h6 class="mb-0">Historial de Comparaciones (Hoy)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Área</th>
                        <th>Sistema (dB)</th>
                        <th>Patrón (dB)</th>
                        <th>Error (%)</th>
                        <th>Fuente / Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pdrCombinado as $r)
                    <tr>
                        <td class="align-middle">{{ $r['hora'] }}</td>
                        <td class="align-middle"><span class="badge bg-info text-dark">{{ $r['area'] }}</span></td>
                        <td class="align-middle fw-bold">{{ $r['iot'] ?? '—' }} dB</td>
                        <td class="align-middle">
                            <input type="number" step="0.1" class="form-control form-control-sm" 
                                   value="{{ $r['patron'] }}" id="patron-{{ $loop->index }}" 
                                   style="width:90px" placeholder="0.0">
                        </td>
                        <td class="align-middle {{ ($r['error'] ?? 0) > 5 ? 'text-danger fw-bold' : 'text-success' }}" id="error-{{ $loop->index }}">
                            {{ $r['error'] !== null ? $r['error'] . '%' : '—' }}
                        </td>
                        <td class="align-middle">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" 
                                       value="{{ $r['fuente'] == '—' ? '' : $r['fuente'] }}" 
                                       id="fuente-{{ $loop->index }}" placeholder="Equipo patrón...">
                                <button class="btn btn-success d-flex align-items-center" 
                                        onclick="savePdrChange({{ $loop->index }}, '{{ $r['hora'] }}', {{ $r['iot'] ?? 'null' }})">
                                    <span class="material-icons" style="font-size:16px">check</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted italic">No se han detectado eventos críticos hoy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const pdrHoras = @json($pdrCombinado->pluck('hora'));
    const pdrIot = @json($pdrCombinado->pluck('iot'));
    const pdrPatron = @json($pdrCombinado->pluck('patron'));
    const pdrError = @json($pdrCombinado->pluck('error'));

    const DEMO_HORAS = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
    const DEMO_PDR_IOT = [72, 78, 85, 80, 88, 91, 76, 70, 79];
    const DEMO_PATRON = [70, 76, 83, 82, 86, 89, 74, 72, 77];
    const DEMO_ERROR = [2.9, 2.6, 2.4, 2.4, 2.3, 2.2, 2.7, 2.8, 2.6];

    const chartL = new Chart(document.getElementById('chartPdrLinea'), {
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
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: 40, max: 120 } }
        }
    });

    const chartE = new Chart(document.getElementById('chartPdrError'), {
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
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    async function savePdrChange(index, hora, iot) {
        const patron = document.getElementById(`patron-${index}`).value;
        const fuente = document.getElementById(`fuente-${index}`).value;

        if(!patron) {
            alert('Por favor ingrese el valor patrón.');
            return;
        }

        try {
            const response = await fetch('{{ route('pdr.ajax-update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    fecha: '{{ date('Y-m-d') }}',
                    hora: hora,
                    patron_db: patron,
                    fuente: fuente,
                    iot_db: iot
                })
            });

            const res = await response.json();
            if(res.success) {
                // Mostrar notificación Toast
                const toastEl = document.getElementById('ajaxToast');
                document.getElementById('ajaxToastMessage').innerText = res.message;
                const toast = new bootstrap.Toast(toastEl);
                toast.show();

                // Recalcular error visualmente
                if(iot) {
                    const error = Math.abs(iot - patron) / patron * 100;
                    const errorTd = document.getElementById(`error-${index}`);
                    errorTd.innerText = error.toFixed(2) + '%';
                    errorTd.className = `align-middle ${error > 5 ? 'text-danger fw-bold' : 'text-success'}`;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al guardar el cambio.');
        }
    }
</script>
@endpush