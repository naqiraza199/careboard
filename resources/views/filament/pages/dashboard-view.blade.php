<x-filament-panels::page>

<livewire:filament.widgets.account-widget />


         @php
            $user = auth()->user();
         @endphp

         @if ($user && $user->hasPermissionTo('admin-dashboard-widgets'))

    <div class="filament-widget dashboard-widget">
          <div class="summary-row">
            <div class="summary-card">
            <h3 class="summary-title">New Incidents</h3>
            <p class="summary-value">0</p>
        </div>
        <div class="summary-card">
            <h3 class="summary-title">Late Clock-ins</h3>
            <p class="summary-value">0</p>
        </div>
        <div class="summary-card">
            <h3 class="summary-title">Late Clock-outs</h3>
            <p class="summary-value">0</p>
        </div>
        <div class="summary-card">
            <h3 class="summary-title">Forms Awaiting Review</h3>
            <p class="summary-value">0</p>
        </div>
        <div class="summary-card">
            <h3 class="summary-title">Shifts Cancelled Today</h3>
            <p class="summary-value">{{ $todayCancelledCount }}</p>
        </div>

    </div>
    <div class="dashboard-grid">

    <div class="chart-card">
        <h3 class="chart-title">Utilisation</h3>
        <div class="chart-wrapper">
            <canvas id="utilisationChart"></canvas>
        </div>
    </div>
        <!-- Charts Section -->
     
        <div class="chart-card">
            <h3 class="chart-title">Vacant Hours vs Availability Forecast <a href="#" class="view-report">View Report</a></h3>
            <div class="chart-wrapper">
                <canvas id="vacantHoursChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3 class="chart-title">Shift Cancellations <a href="#" class="view-report">View Report</a></h3>
            <div class="chart-wrapper">
                <canvas id="shiftCancellationsChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3 class="chart-title">Late Clock In Times <a href="#" class="view-report">View Report</a></h3>
            <div class="chart-wrapper">
                <canvas id="lateClockInChart"></canvas>
            </div>
        </div>
     <div class="chart-card wide mt-8">
    <h3 class="chart-title text-lg font-bold mb-2">
        Rostered vs Actual Shift Time Variance
        <a href="#" class="text-blue-600 text-sm hover:underline float-right">View Report</a>
    </h3>
    <div class="chart-wrapper-hehe flex flex-wrap gap-4">
        <div class="chart-half flex-1 min-w-[300px]">
            <canvas id="shiftVarianceChart" height="500"></canvas>
        </div>
        <div class="chart-half flex-1 min-w-[300px]">
            <table class="variance-table w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b text-left text-gray-700 font-semibold">
                        <th class="py-2">Staff Name</th>
                        <th>Variance Rate</th>
                        <th>Variance Trend</th>
                    </tr>
                </thead>
                <tbody id="varianceTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</div>
         @endif

<style>
    .dashboard-widget {
        font-family: 'Poppins', sans-serif;
        border-radius: 15px;
    }

    /* ── Summary Row ── */
    .summary-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        width: 100%;
    }
    .summary-card {
        flex: 1 1 160px;
        min-width: 130px;
        background: #fff;
        padding: 18px 14px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        text-align: center;
        border: 1px solid #e5e7eb;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.10);
    }
    .summary-title {
        color: #6b7280;
        font-size: 11px;
        margin-bottom: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .summary-value {
        color: #111827;
        font-size: 26px;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }

    /* ── Dashboard Grid ── */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
        margin-top: 28px;
    }
    .chart-card {
        padding: 22px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        background: #fff;
        border: 1px solid #e5e7eb;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        min-width: 0;
    }
    .chart-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.10);
    }
    .chart-card.wide {
        grid-column: span 2;
    }
    .chart-title {
        color: #1f2937;
        font-size: 14px;
        margin-bottom: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px;
    }
    .view-report {
        color: #3b82f6;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        transition: color 0.2s ease;
    }
    .view-report:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }
    .chart-wrapper {
        height: 280px;
        position: relative;
    }
    .chart-wrapper-hehe {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
        box-sizing: border-box;
    }
    .chart-half {
        flex: 1 1 280px;
        min-width: 0;
        height: 320px;
        position: relative;
        overflow: hidden;
    }
    .chart-half canvas {
        position: absolute !important;
        top: 0; left: 0;
        width: 100% !important;
        height: 100% !important;
    }
    canvas {
        width: 100% !important;
        border-radius: 5px;
    }
    .chart-card.wide .chart-half:last-child {
        height: auto;
        overflow-y: auto;
        max-height: 320px;
    }
    .variance-table {
        width: 100%;
        border-collapse: collapse;
    }
    .variance-table th {
        padding: 10px 8px;
        border-bottom: 2px solid #e5e7eb;
        text-align: left;
        font-size: 12px;
        color: #374151;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .variance-table td {
        padding: 10px 8px;
        border-bottom: 1px solid #f3f4f6;
        text-align: left;
        font-size: 13px;
        color: #6b7280;
    }
    .trend-line {
        border: 0;
        height: 3px;
        background: linear-gradient(90deg, #3b82f6, #93c5fd);
        margin: 5px 0;
        border-radius: 2px;
    }

    /* ── Responsive ── */
    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        .chart-card.wide {
            grid-column: span 1;
        }
    }
    @media (max-width: 640px) {
        .summary-row {
            gap: 10px;
        }
        .summary-card {
            flex: 1 1 calc(50% - 10px);
            min-width: 120px;
            padding: 14px 10px;
        }
        .summary-value {
            font-size: 22px;
        }
        .dashboard-grid {
            gap: 14px;
            margin-top: 18px;
        }
        .chart-wrapper {
            height: 220px;
        }
        .chart-half {
            height: 240px;
        }
    }
</style>
      
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Please check the CDN link.');
        return;
    }
 const chartData = @json($chartData);

        if (chartData && chartData.labels.length > 0) {
            const ctx = document.getElementById('utilisationChart').getContext('2d');

            // Create smooth gradients for each dataset
            const gradientAssigned = ctx.createLinearGradient(0, 0, 400, 0);
            gradientAssigned.addColorStop(0, 'rgba(39, 35, 17, 0.9)');
            gradientAssigned.addColorStop(1, 'rgba(17, 24, 39, 0.5)');

            const gradientContractual = ctx.createLinearGradient(0, 0, 400, 0);
            gradientContractual.addColorStop(0, 'rgba(209, 213, 219, 0.8)');
            gradientContractual.addColorStop(1, 'rgba(229, 231, 235, 0.5)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Assigned Hours',
                            data: chartData.assigned_hours,
                            backgroundColor: gradientAssigned,
                            borderWidth: 0,
                            borderRadius: 6,
                            barThickness: 16
                        },
                        {
                            label: 'Contractual Hours',
                            data: chartData.contractual_hours,
                            backgroundColor: gradientContractual,
                            borderWidth: 0,
                            borderRadius: 6,
                            barThickness: 16
                        }
                    ]
                },
                options: {
                    indexAxis: 'y', // ✅ Horizontal layout
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: 10 },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 12 }
                            }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                color: '#111827',
                                font: { size: 13, weight: '600' }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'start',
                            labels: {
                                color: '#1f2937',
                                boxWidth: 14,
                                font: { size: 13, weight: '600' },
                                padding: 16
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(31,41,55,0.9)',
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            callbacks: {
                                label: (context) =>
                                    `${context.dataset.label}: ${context.parsed.x} hrs`
                            }
                        }
                    }
                }
            });
        } else {
            console.warn("No data available for the chart.");
        }
    // Vacant Hours vs Availability Forecast
    const vacantHoursChart = new Chart(document.getElementById('vacantHoursChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Oct 04', 'Oct 06', 'Oct 08', 'Oct 10', 'Oct 12', 'Oct 14', 'Oct 16', 'Oct 18', 'Oct 20', 'Oct 22', 'Oct 24', 'Oct 26', 'Oct 28', 'Oct 30', 'Nov 01'],
            datasets: [{
                label: 'Vacant Hours',
                data: [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 90, 80, 70, 60],
                borderColor: 'rgba(255, 159, 64, 1)',
                fill: false,
                tension: 0.4
            }, {
                label: 'Availability (%)',
                data: [100, 90, 80, 70, 60, 50, 40, 30, 20, 10, 0, 10, 20, 30, 40],
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'top', labels: { font: { size: 14 } } } },
            responsive: true,
            maintainAspectRatio: false
        }
    });

 const shiftCancellationData = @json($shiftCancellationData);

    if (shiftCancellationData && shiftCancellationData.labels.length > 0) {
        const ctx = document.getElementById('shiftCancellationsChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar', // switched from line → bar (more logical for totals)
            data: {
                labels: shiftCancellationData.labels,
                datasets: [{
                    label: 'Total Cancellations',
                    data: shiftCancellationData.counts,
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.7)', // blue for staff
                        'rgba(239, 68, 68, 0.7)', // red for client
                    ],
                    borderColor: [
                        'rgba(37, 99, 235, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    borderWidth: 1,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { font: { size: 13, weight: 'bold' }, color: '#111827' },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} total cancellations`,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { color: '#6b7280', font: { size: 12 } },
                        title: {
                            display: true,
                            text: 'Cancellations',
                            color: '#374151',
                            font: { size: 13, weight: '600' },
                        },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#111827', font: { size: 12 } },
                    },
                },
            },
        });
    }

  const shiftVarianceData = @json($shiftVarianceData);

    if (shiftVarianceData && shiftVarianceData.labels.length > 0) {
        const ctx2 = document.getElementById('shiftVarianceChart').getContext('2d');

        // Modern gradient for horizontal bars
        const gradient = ctx2.createLinearGradient(0, 0, 400, 0);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.9)');   // Tailwind blue-500
        gradient.addColorStop(1, 'rgba(96, 165, 250, 0.3)');   // Tailwind blue-400

        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: shiftVarianceData.labels,
                datasets: [{
                    label: 'Variance Rate',
                    data: shiftVarianceData.variance_rate,
                    backgroundColor: gradient,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 10,
                    barThickness: 18,
                    hoverBackgroundColor: 'rgba(37, 99, 235, 0.9)',
                }]
            },
            options: {
                indexAxis: 'y', // ✅ Horizontal bars
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                layout: { padding: 10 },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            color: '#6b7280',
                            font: { size: 12 },
                            callback: (value) => value + '%'
                        },
                        title: {
                            display: true,
                            text: 'Variance Rate (%)',
                            color: '#374151',
                            font: { size: 13, weight: '600' }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: '#111827',
                            font: { size: 13, weight: '600' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#1f2937',
                            font: { size: 13, weight: 'bold' },
                            boxWidth: 18
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.x}% variance`;
                            }
                        }
                    }
                }
            }
        });

        // ✅ Table stays the same
        const tableBody = document.getElementById('varianceTableBody');
        tableBody.innerHTML = shiftVarianceData.labels.map((name, index) => `
            <tr class="border-b">
                <td class="py-2">${name}</td>
                <td>${shiftVarianceData.variance_rate[index]}%</td>
                <td><hr class="trend-line border-t border-gray-300"></td>
            </tr>
        `).join('');
    }

    // Late Clock In Times
    const lateClockInChart = new Chart(document.getElementById('lateClockInChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Sep 04', 'Sep 06', 'Sep 08', 'Sep 10', 'Sep 12', 'Sep 14', 'Sep 16', 'Sep 18', 'Sep 20', 'Sep 22', 'Sep 24', 'Sep 26', 'Sep 28', 'Oct 01', 'Oct 03'],
            datasets: [{
                label: 'Staff',
                data: [0, 5, 10, 15, 20, 25, 20, 15, 10, 5, 0, 5, 10, 15, 20],
                borderColor: 'rgba(255, 159, 64, 1)',
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'top', labels: { font: { size: 14 } } } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>


</x-filament-panels::page>
