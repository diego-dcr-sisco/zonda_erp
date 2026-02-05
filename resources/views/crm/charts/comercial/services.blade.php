@php
    use Carbon\Carbon;
    $date = Carbon::now();
@endphp


<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title fw-bold d-flex justify-content-between">
                <span class="fs-5 fw-bold">Servicios por tipo de cliente</span>
                <div class="input-group w-50">
                    <div class="input-group w-100 mb-3">
                        <!-- Selectores de mes y año -->
                        <select class="form-select" id="yearServicesSelector">
                            @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        <select class="form-select" id="monthServicesSelector">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                    {{ Carbon::create()->month($i)->locale('es')->monthName }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </h5>
            <div id="monthlyServicesPieChartContainer">
                <canvas id="monthlyServicesPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let servicesChart;
let servicesYear;
let servicesMonth;

function fetchServicesData(year, month) {
    fetch(`/crm/chart/services-by-type?year=${year}&month=${month}`)
        .then(response => response.json())
        .then(data => {
            renderServicesChart(data);
        })
        .catch(error => console.error('Error fetching services data:', error));
}

function renderServicesChart(data) {
    const ctx = document.getElementById('monthlyServicesPieChart').getContext('2d');
    if (servicesChart) servicesChart.destroy();
    servicesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Domésticos', 'Comerciales', 'Industrial/Planta'],
            datasets: [
                {
                    label: 'Servicios',
                    data: [data.domestics, data.comercials, data.industrials],
                    backgroundColor: ['#039BE5', '#1A237E', '#4CAF50'],
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Servicios por tipo de cliente' }
            }
        }
    });
}

// Inicialización
servicesYear = document.getElementById('yearServicesSelector').value;
servicesMonth = document.getElementById('monthServicesSelector').value;
fetchServicesData(servicesYear, servicesMonth);

document.getElementById('yearServicesSelector').addEventListener('change', function() {
    fetchServicesData(this.value, document.getElementById('monthServicesSelector').value);
});
document.getElementById('monthServicesSelector').addEventListener('change', function() {
    fetchServicesData(document.getElementById('yearServicesSelector').value, this.value);
});
</script>
