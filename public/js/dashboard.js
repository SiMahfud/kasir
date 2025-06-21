document.addEventListener('DOMContentLoaded', function () {
    const salesChartCanvas = document.getElementById('salesLast7DaysChart');

    if (salesChartCanvas && typeof Chart !== 'undefined' && typeof chartSalesLabels !== 'undefined' && typeof chartSalesData !== 'undefined') {
        const ctx = salesChartCanvas.getContext('2d');

        new Chart(ctx, {
            type: 'line', // or 'bar'
            data: {
                labels: chartSalesLabels,
                datasets: [{
                    label: 'Total Sales (IDR)',
                    data: chartSalesData,
                    borderColor: 'rgb(59, 130, 246)', // Tailwind blue-500
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.2,
                    fill: true,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(59, 130, 246)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Allows chart to fill container height
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                // Simple currency formatting for ticks
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: {
                            // color: 'rgba(200, 200, 200, 0.2)', // Lighter grid lines
                        }
                    },
                    x: {
                         grid: {
                            display: false, // Hide vertical grid lines for cleaner look
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true, // Keep legend for single dataset, or set to false if obvious
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += parseFloat(context.parsed.y).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 });
                                }
                                return label;
                            }
                        }
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                }
            }
        });
    } else {
        if (!salesChartCanvas) console.error('Dashboard Info: Chart canvas not found.');
        if (typeof Chart === 'undefined') console.error('Dashboard Info: Chart.js library not loaded.');
        if (typeof chartSalesLabels === 'undefined') console.error('Dashboard Info: chartSalesLabels not defined.');
        if (typeof chartSalesData === 'undefined') console.error('Dashboard Info: chartSalesData not defined.');
    }
});
