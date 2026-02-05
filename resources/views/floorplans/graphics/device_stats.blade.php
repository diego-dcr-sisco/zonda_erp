@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('floorplan.devices', ['id' => $device->floorplan_id, 'version' => $device->version]) }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">Estadísticas del dispositivo</span>
        </div>
        <div class="row m-3">
            <div class="col-12">
                <div class="border rounded shadow p-3">
                    <h5 class="fw-bold">Últimas 10 revisiones</h5>
                    @if(isset($reviews) && $reviews->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Pregunta</th>
                                        <th>Respuesta</th>
                                        <th>Orden</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reviews as $rev)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($rev->updated_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $rev->question?->text ?? 'Pregunta' }}</td>
                                            <td>{{ $rev->answer }}</td>
                                            <td>
                                                @if($rev->order)
                                                    <a href="{{ route('order.show', ['id' => $rev->order->id, 'section' => 1]) }}">#{{ $rev->order->folio }}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">No se encontraron revisiones recientes para este dispositivo.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 m-3">
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <h5 class="fw-bold">Incidencias por tipo de plaga - {{ $device->code ?? 'Dispositivo' }}</h5>

                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label class="form-label">Plano</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $floorplan->filename }}" disabled>
                        </div>
                        <div class="col-3">
                            <label class="form-label">Mes</label>
                            <select id="device-month" class="form-select form-select-sm">
                                @foreach($months as $index => $m)
                                    <option value="{{ $index }}" @if($index == \Carbon\Carbon::now()->month) selected @endif>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label">Año</label>
                            <select id="device-year" class="form-select form-select-sm">
                                @foreach($years as $y)
                                    <option value="{{ $y }}" @if($y == \Carbon\Carbon::now()->year) selected @endif>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3 d-flex align-items-end">
                            <button id="search-device-pests" class="btn btn-primary btn-sm">Buscar</button>
                        </div>
                    </div>

                    <div class="position-relative">
                        <div id="pests-loader" class="spinner-border spinner-border-sm position-absolute" style="display:none; top: 10px; right: 10px;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="devicePestsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <h5 class="fw-bold">Tendencia mensual - {{ $device->code ?? 'Dispositivo' }}</h5>

                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label class="form-label">Año</label>
                            <select id="trend-year" class="form-select form-select-sm">
                                @foreach($years as $y)
                                    <option value="{{ $y }}" @if($y == \Carbon\Carbon::now()->year) selected @endif>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-9 d-flex align-items-end justify-content-end">
                            <button id="search-device-trend" class="btn btn-primary btn-sm">Buscar</button>
                        </div>
                    </div>

                    <div class="position-relative">
                        <div id="trend-loader" class="spinner-border spinner-border-sm position-absolute" style="display:none; top: 10px; right: 10px;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="deviceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const deviceId = {{ $device->id }};
        const floorplanId = {{ $floorplan->id }};
        let pestsChart = null;
        let trendChart = null;

        function updatePestsChart(labels, data) {
            const ctx = document.getElementById('devicePestsChart').getContext('2d');
            if (pestsChart) pestsChart.destroy();
            pestsChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: labels, datasets: [{ label: 'Incidentes por plaga', data: data, backgroundColor: 'rgba(222,82,59,0.5)', borderColor: 'rgba(222,82,59)', borderWidth: 1 }] },
                options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });
        }

        function updateTrendChart(labels, data) {
            const ctx = document.getElementById('deviceTrendChart').getContext('2d');
            if (trendChart) trendChart.destroy();
            trendChart = new Chart(ctx, {
                type: 'line',
                data: { labels: labels, datasets: [{ label: 'Incidentes por mes', data: data, borderColor: 'rgba(75,192,75)', backgroundColor: 'rgba(75,192,75,0.1)', fill: true, tension: 0.4 }] },
                options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });
        }

        // Inicializar con los datos pasados desde el controlador
        document.addEventListener('DOMContentLoaded', function() {
            updatePestsChart({!! json_encode($graph_per_pests['labels']) !!}, {!! json_encode($graph_per_pests['data']) !!});
            updateTrendChart({!! json_encode($graph_per_months['labels']) !!}, {!! json_encode($graph_per_months['data']) !!});
        });

        async function fetchDeviceData(month, year, trend = false) {
            try {
                const url = `{{ url('floorplans/devices') }}/${floorplanId}/device/${deviceId}/stats?month=${month}&year=${year}${trend? '&trend=1' : ''}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                return await res.json();
            } catch (e) {
                console.error(e);
                return null;
            }
        }

        document.getElementById('search-device-pests').addEventListener('click', async function() {
            const month = document.getElementById('device-month').value;
            const year = document.getElementById('device-year').value;
            document.getElementById('pests-loader').style.display = 'block';
            const data = await fetchDeviceData(month, year, false);
            if (data && data.success) updatePestsChart(data.pests.labels, data.pests.data);
            document.getElementById('pests-loader').style.display = 'none';
        });

        document.getElementById('search-device-trend').addEventListener('click', async function() {
            const year = document.getElementById('trend-year').value;
            document.getElementById('trend-loader').style.display = 'block';
            const data = await fetchDeviceData(1, year, true);
            if (data && data.success) updateTrendChart(data.trend.labels, data.trend.data);
            document.getElementById('trend-loader').style.display = 'none';
        });
    </script>
@endsection
