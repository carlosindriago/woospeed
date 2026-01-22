document.addEventListener('DOMContentLoaded', function () {
    const chartCanvas = document.getElementById('speedChart');
    if (!chartCanvas) return; // Prevention

    const ctx = chartCanvas.getContext('2d');
    let speedChart = null;
    let currentDays = 30;

    // Formatear moneda
    function formatCurrency(value) {
        return '$' + parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Inicializar Chart
    function initChart(data) {
        speedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.report_date),
                datasets: [{
                    label: woospeed_dashboard_vars.i18n.revenue,
                    data: data.map(d => parseFloat(d.total_sales)),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Renderizar Leaderboard
    function renderLeaderboard(items) {
        const container = document.getElementById('leaderboard-container');
        if (!items || items.length === 0) {
            container.innerHTML = `<div class="ws-loading">${woospeed_dashboard_vars.i18n.no_data}</div>`;
            return;
        }
        container.innerHTML = items.map((item, i) => `
            <div class="ws-leaderboard-item">
                <span class="ws-leaderboard-rank ${i === 0 ? 'gold' : ''}">${i + 1}</span>
                <span class="ws-leaderboard-name">${item.product_name}</span>
                <span class="ws-leaderboard-sold">${item.total_sold} ${woospeed_dashboard_vars.i18n.sold}</span>
            </div>
        `).join('');
    }

    // Cargar Dashboard
    function loadDashboard() {
        const startTime = performance.now();

        // ajaxurl is defined by WordPress in Admin
        fetch(ajaxurl + '?action=woospeed_get_data&days=' + currentDays)
            .then(res => res.json())
            .then(response => {
                if (!response.success) return;
                const { kpis, chart, leaderboard } = response.data;

                // KPIs
                const revenueEl = document.getElementById('kpi-revenue');
                if (revenueEl) revenueEl.textContent = formatCurrency(kpis.revenue);

                const ordersEl = document.getElementById('kpi-orders');
                if (ordersEl) ordersEl.textContent = kpis.orders.toLocaleString();

                const aovEl = document.getElementById('kpi-aov');
                if (aovEl) aovEl.textContent = formatCurrency(kpis.aov);

                const maxEl = document.getElementById('kpi-max');
                if (maxEl) maxEl.textContent = formatCurrency(kpis.max_order);

                // Chart
                if (!speedChart) {
                    initChart(chart);
                } else {
                    speedChart.data.labels = chart.map(d => d.report_date);
                    speedChart.data.datasets[0].data = chart.map(d => parseFloat(d.total_sales));
                    speedChart.update('none');
                }

                // Leaderboard
                renderLeaderboard(leaderboard);

                // Query Time
                const elapsed = ((performance.now() - startTime) / 1000).toFixed(3);
                const timeEl = document.getElementById('ws-query-time');
                if (timeEl) timeEl.textContent = woospeed_dashboard_vars.i18n.load_time + ': ' + elapsed + 's';
            })
            .catch(err => console.error('Dashboard error:', err));
    }

    // Date Range Change
    const dateRange = document.getElementById('ws-date-range');
    if (dateRange) {
        dateRange.addEventListener('change', function () {
            currentDays = parseInt(this.value);
            loadDashboard();
        });
    }

    // Initial Load
    loadDashboard();

    // Auto-refresh cada 10s
    setInterval(loadDashboard, 10000);
});
