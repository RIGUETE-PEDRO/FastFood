document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const dashboardDataEl = document.getElementById('dashboard-data');
    const statusCtx = document.getElementById('pedidosStatusChart');
    const topProdutosCtx = document.getElementById('topProdutosChart');

    if (!dashboardDataEl || (!statusCtx && !topProdutosCtx)) {
        return;
    }

    const dashboardData = JSON.parse(dashboardDataEl.textContent || '{}');

    const statusLabels = dashboardData.statusLabels || [];
    const statusValores = dashboardData.statusValores || [];
    const topProdutosLabels = dashboardData.topProdutosLabels || [];
    const topProdutosValores = dashboardData.topProdutosValores || [];

    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Pedidos',
                    data: statusValores,
                    backgroundColor: [
                        'rgba(214, 40, 40, 0.85)',
                        'rgba(255, 123, 0, 0.85)',
                        'rgba(11, 46, 91, 0.85)',
                        'rgba(22, 163, 74, 0.85)',
                        'rgba(107, 114, 128, 0.85)'
                    ],
                    borderRadius: 8,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    if (topProdutosCtx) {
        new Chart(topProdutosCtx, {
            type: 'bar',
            data: {
                labels: topProdutosLabels,
                datasets: [{
                    label: 'Unidades vendidas',
                    data: topProdutosValores,
                    backgroundColor: 'rgba(255, 123, 0, 0.85)',
                    borderColor: 'rgba(214, 40, 40, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 42
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
