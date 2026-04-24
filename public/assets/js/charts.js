document.addEventListener('DOMContentLoaded', () => {
    // Sales Chart
    const salesEl = document.getElementById("salesChart");
    if (salesEl && salesEl.dataset.chart) {
        const data = JSON.parse(salesEl.dataset.chart);
        new Chart(salesEl, {
            type: 'bar',
            data: {
                labels: data.map(x => x.month_key),
                datasets: [{
                    label: 'Ventes (EUR)',
                    data: data.map(x => Number(x.total_sales || 0)),
                    backgroundColor: '#0f766e',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e2e6ea' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Warehouse Chart
    const whEl = document.getElementById("warehouseChart");
    if (whEl && whEl.dataset.chart) {
        const data = JSON.parse(whEl.dataset.chart);
        new Chart(whEl, {
            type: 'doughnut',
            data: {
                labels: data.map(x => x.warehouse_name),
                datasets: [{
                    data: data.map(x => Number(x.total_qty || 0)),
                    backgroundColor: ['#f59e0b', '#0f766e', '#3b82f6', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                },
                cutout: '70%'
            }
        });
    }
});
