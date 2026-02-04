@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <!-- <a href="{{ route('order.index') }}" class="text-decoration-none pe-3">
                                                                <i class="bi bi-arrow-left fs-4"></i>
                                                            </a> -->
            <a href="#" onclick="window.history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                Consultar Grafico
            </span>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 m-3">
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <div class="mb-3">
                        <h4 class="fw-bold">Incidencias por dispositivo</h4>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Plano </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->filename }}" disabled>
                            <input type="hidden" id="floorplan-id" name="floorplan_id" value="{{ $floorplan->id }}">
                        </div>

                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Servicio </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->service->name ?? 'No aplica' }}" disabled>
                        </div>

                        <div class="col-2">
                            <label for="floorplan-name" class="form-label is-required">Version</label>
                            <select class="form-select form-select-sm filter-select" id="floorplan-version-device" name="version">
                                @forelse ($floorplan->versions as $version)
                                    <option value="{{ $version->version }}"
                                        @if ($version->version == $floorplan->version) selected @endif>
                                        {{ $version->version }}</option>
                                @empty
                                    <option value="" selected>Sin version</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-4">
                            <label for="floorplan-name" class="form-label is-required">Mes/Año</label>
                            <div class="input-group input-group-sm">
                                <select class="form-select filter-select" id="floorplan-month-device" name="month">
                                    @forelse ($months as $index => $month)
                                        <option value="{{ $index }}"
                                            @if ($index == Carbon\Carbon::now()->month) selected @endif>
                                            {{ $month }}</option>
                                    @empty
                                        <option value="" selected>Sin version</option>
                                    @endforelse
                                </select>
                                <select class="form-select form-select-sm filter-select" id="floorplan-year-device" name="year">
                                    @forelse ($years as $year)
                                        <option value="{{ $year }}"
                                            @if ($year == Carbon\Carbon::now()->year) selected @endif>
                                            {{ $year }}</option>
                                    @empty
                                        <option value="" selected>Sin año</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" id="search-devices-btn">Buscar</button>
                    </div>

                    <div class="position-relative">
                        <div id="devices-loader" class="spinner-border spinner-border-sm position-absolute" role="status" style="display:none; top: 10px; right: 10px;">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="devicesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <div class="mb-3">
                        <h4 class="fw-bold">Incidencias por tipo de plaga</h4>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Plano </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->filename }}" disabled>
                            <input type="hidden" id="floorplan-id" name="floorplan_id" value="{{ $floorplan->id }}">
                        </div>

                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Servicio </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->service->name ?? 'No aplica' }}" disabled>
                        </div>

                        <div class="col-2">
                            <label for="floorplan-name" class="form-label is-required">Version</label>
                            <select class="form-select form-select-sm filter-select" id="floorplan-version-pests" name="version">
                                @forelse ($floorplan->versions as $version)
                                    <option value="{{ $version->version }}"
                                        @if ($version->version == $floorplan->version) selected @endif>
                                        {{ $version->version }}</option>
                                @empty
                                    <option value="" selected>Sin version</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-4">
                            <label for="floorplan-name" class="form-label is-required">Mes/Año</label>
                            <div class="input-group input-group-sm">
                                <select class="form-select filter-select" id="floorplan-month-pests" name="month">
                                    @forelse ($months as $index => $month)
                                        <option value="{{ $index }}"
                                            @if ($index == Carbon\Carbon::now()->month) selected @endif>
                                            {{ $month }}</option>
                                    @empty
                                        <option value="" selected>Sin version</option>
                                    @endforelse
                                </select>
                                <select class="form-select form-select-sm filter-select" id="floorplan-year-pests" name="year">
                                    @forelse ($years as $year)
                                        <option value="{{ $year }}"
                                            @if ($year == Carbon\Carbon::now()->year) selected @endif>
                                            {{ $year }}</option>
                                    @empty
                                        <option value="" selected>Sin año</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" id="search-pests-btn">Buscar</button>
                    </div>

                    <div class="position-relative">
                        <div id="pests-loader" class="spinner-border spinner-border-sm position-absolute" style="display:none; top: 10px; right: 10px;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="pestsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let devicesChart = null;
        let pestsChart = null;
        const floorplanId = document.getElementById('floorplan-id').value;

        // Función para cargar los datos de incidentes vía AJAX
        async function fetchGraphData(version, month, year) {
            try {
                const response = await fetch(`{{ route('floorplan.graphic.incidents', $floorplan->id) }}?version=${version}&month=${month}&year=${year}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }

                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error al cargar datos:', error);
                alert('Error al cargar los datos del gráfico');
                return null;
            }
        }

        // Función para actualizar el gráfico de dispositivos
        function updateDevicesChart(labels, data) {
            const ctx_d = document.getElementById('devicesChart').getContext('2d');

            if (devicesChart) {
                devicesChart.destroy();
            }

            devicesChart = new Chart(ctx_d, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Incidentes por dispositivo',
                        data: data,
                        borderWidth: 1,
                        borderColor: 'rgba(2, 38, 90)',
                        backgroundColor: 'rgba(2, 38, 90, 0.5)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Función para actualizar el gráfico de plagas
        function updatePestsChart(labels, data) {
            const ctx_p = document.getElementById('pestsChart').getContext('2d');

            if (pestsChart) {
                pestsChart.destroy();
            }

            pestsChart = new Chart(ctx_p, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Incidentes por plaga',
                        data: data,
                        borderColor: 'rgba(222, 82, 59)',
                        backgroundColor: 'rgba(222, 82, 59, 0.5)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Inicializar gráficos con datos iniciales
        function initializeCharts() {
            updateDevicesChart({!! json_encode($graph_per_devices['labels']) !!}, {!! json_encode($graph_per_devices['data']) !!});
            updatePestsChart({!! json_encode($graph_per_pests['labels']) !!}, {!! json_encode($graph_per_pests['data']) !!});
        }

        // Event listeners para los botones de búsqueda
        document.getElementById('search-devices-btn').addEventListener('click', async function() {
            const version = document.getElementById('floorplan-version-device').value;
            const month = document.getElementById('floorplan-month-device').value;
            const year = document.getElementById('floorplan-year-device').value;

            if (!version || !month || !year) {
                alert('Por favor, complete todos los filtros');
                return;
            }

            document.getElementById('devices-loader').style.display = 'block';

            const graphData = await fetchGraphData(version, month, year);
            if (graphData && graphData.success) {
                updateDevicesChart(graphData.devices.labels, graphData.devices.data);
            }

            document.getElementById('devices-loader').style.display = 'none';
        });

        document.getElementById('search-pests-btn').addEventListener('click', async function() {
            const version = document.getElementById('floorplan-version-pests').value;
            const month = document.getElementById('floorplan-month-pests').value;
            const year = document.getElementById('floorplan-year-pests').value;

            if (!version || !month || !year) {
                alert('Por favor, complete todos los filtros');
                return;
            }

            document.getElementById('pests-loader').style.display = 'block';

            const graphData = await fetchGraphData(version, month, year);
            if (graphData && graphData.success) {
                updatePestsChart(graphData.pests.labels, graphData.pests.data);
            }

            document.getElementById('pests-loader').style.display = 'none';
        });

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
    </script>
@endsection
