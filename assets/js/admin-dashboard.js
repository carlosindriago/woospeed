document.addEventListener('DOMContentLoaded', function () {
    const chartCanvas = document.getElementById('speedChart');
    if (!chartCanvas) return;

    const ctx = chartCanvas.getContext('2d');
    let speedChart = null;

    // ========================================
    // Date Range State
    // ========================================
    let currentPreset = 'month_to_date';
    let startDate = null;
    let endDate = null;
    let compareType = 'previous_period';

    // Format date to YYYY-MM-DD
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    // Format for display (e.g., "Jan 1 - 22, 2026")
    function formatDateRange(start, end) {
        const options = { month: 'short', day: 'numeric' };
        const startStr = start.toLocaleDateString('en-US', options);
        const endStr = end.toLocaleDateString('en-US', { ...options, year: 'numeric' });
        return `(${startStr} - ${endStr})`;
    }

    // Calculate date range based on preset
    function calculatePresetDates(preset) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        let start, end;

        switch (preset) {
            case 'today':
                start = new Date(today);
                end = new Date(today);
                break;
            case 'yesterday':
                start = new Date(today);
                start.setDate(start.getDate() - 1);
                end = new Date(start); // Clone start after modification
                break;
            case 'week_to_date':
                start = new Date(today);
                const dayOfWeek = start.getDay();
                const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Monday = start
                start.setDate(start.getDate() - diff);
                end = new Date(today);
                break;
            case 'last_week':
                start = new Date(today);
                const currentDay = start.getDay();
                const daysToLastMonday = currentDay === 0 ? 6 : currentDay - 1;
                start.setDate(start.getDate() - daysToLastMonday - 7);
                end = new Date(start);
                end.setDate(end.getDate() + 6);
                break;
            case 'month_to_date':
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today);
                break;
            case 'last_month':
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'quarter_to_date':
                const currentQuarter = Math.floor(today.getMonth() / 3);
                start = new Date(today.getFullYear(), currentQuarter * 3, 1);
                end = new Date(today);
                break;
            case 'last_quarter':
                const lastQuarter = Math.floor(today.getMonth() / 3) - 1;
                const year = lastQuarter < 0 ? today.getFullYear() - 1 : today.getFullYear();
                const quarter = lastQuarter < 0 ? 3 : lastQuarter;
                start = new Date(year, quarter * 3, 1);
                end = new Date(year, (quarter + 1) * 3, 0);
                break;
            case 'year_to_date':
                start = new Date(today.getFullYear(), 0, 1);
                end = new Date(today);
                break;
            case 'last_year':
                start = new Date(today.getFullYear() - 1, 0, 1);
                end = new Date(today.getFullYear() - 1, 11, 31);
                break;
            default:
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today);
        }

        return { start, end };
    }

    // Update date picker UI
    function updateDatePickerUI() {
        const labelEl = document.getElementById('ws-date-label');
        const rangeTextEl = document.getElementById('ws-date-range-text');

        if (labelEl && woospeed_dashboard_vars.i18n.presets) {
            labelEl.textContent = woospeed_dashboard_vars.i18n.presets[currentPreset] || currentPreset;
        }

        if (rangeTextEl && startDate && endDate) {
            rangeTextEl.textContent = formatDateRange(startDate, endDate);
        }
    }

    // Initialize date picker
    function initDatePicker() {
        const trigger = document.getElementById('ws-date-trigger');
        const dropdown = document.getElementById('ws-date-dropdown');
        const tabs = document.querySelectorAll('.ws-date-tab');
        const presetBtns = document.querySelectorAll('.ws-preset-btn');
        const updateBtn = document.getElementById('ws-date-update');
        const customStart = document.getElementById('ws-custom-start');
        const customEnd = document.getElementById('ws-custom-end');

        if (!trigger || !dropdown) return;

        // Toggle dropdown
        trigger.addEventListener('click', function () {
            trigger.classList.toggle('active');
            dropdown.classList.toggle('open');
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.ws-date-picker')) {
                trigger.classList.remove('active');
                dropdown.classList.remove('open');
            }
        });

        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const tabName = this.dataset.tab;
                document.getElementById('ws-panel-presets').style.display = tabName === 'presets' ? 'block' : 'none';
                document.getElementById('ws-panel-custom').style.display = tabName === 'custom' ? 'block' : 'none';
            });
        });

        // Preset selection
        presetBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                presetBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentPreset = this.dataset.preset;

                const dates = calculatePresetDates(currentPreset);
                startDate = dates.start;
                endDate = dates.end;

                // Update custom inputs to match
                if (customStart && customEnd) {
                    customStart.value = formatDate(startDate);
                    customEnd.value = formatDate(endDate);
                }
            });
        });

        // Compare option
        document.querySelectorAll('input[name="ws-compare"]').forEach(radio => {
            radio.addEventListener('change', function () {
                compareType = this.value;
            });
        });

        // Update button
        if (updateBtn) {
            updateBtn.addEventListener('click', function () {
                // Check if custom tab is active
                const customPanel = document.getElementById('ws-panel-custom');
                if (customPanel && customPanel.style.display !== 'none') {
                    if (customStart && customEnd && customStart.value && customEnd.value) {
                        startDate = new Date(customStart.value);
                        endDate = new Date(customEnd.value);
                        currentPreset = 'custom';
                    }
                }

                // Close dropdown
                trigger.classList.remove('active');
                dropdown.classList.remove('open');

                // Update UI and reload
                updateDatePickerUI();
                loadDashboard();
            });
        }

        // Calculate initial dates
        const initialDates = calculatePresetDates(currentPreset);
        startDate = initialDates.start;
        endDate = initialDates.end;

        // Set initial custom input values
        if (customStart && customEnd) {
            customStart.value = formatDate(startDate);
            customEnd.value = formatDate(endDate);
        }

        updateDatePickerUI();
    }

    // ========================================
    // Dashboard Functions
    // ========================================

    function formatCurrency(value) {
        return '$' + parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

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

    function renderLeaderboard(items) {
        const container = document.getElementById('leaderboard-container');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `<div class="ws-empty-state">
                <div class="ws-empty-state-icon">📦</div>
                <div class="ws-empty-state-text">${woospeed_dashboard_vars.i18n.no_data}</div>
            </div>`;
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

    function renderBottomProducts(items) {
        const container = document.getElementById('bottom-products-container');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `<div class="ws-empty-state">
                <div class="ws-empty-state-icon">📊</div>
                <div class="ws-empty-state-text">${woospeed_dashboard_vars.i18n.no_data}</div>
            </div>`;
            return;
        }
        container.innerHTML = items.map((item, i) => `
            <div class="ws-bottom-item">
                <span class="ws-bottom-rank">${i + 1}</span>
                <span class="ws-bottom-name">${item.product_name}</span>
                <span class="ws-bottom-sold">${item.total_sold} ${woospeed_dashboard_vars.i18n.sold}</span>
            </div>
        `).join('');
    }

    function renderCategories(items) {
        const container = document.getElementById('categories-container');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `<div class="ws-empty-state">
                <div class="ws-empty-state-icon">📁</div>
                <div class="ws-empty-state-text">${woospeed_dashboard_vars.i18n.no_data}</div>
            </div>`;
            return;
        }
        container.innerHTML = items.map(item => `
            <div class="ws-category-item">
                <span class="ws-category-name">${item.category_name}</span>
                <span class="ws-category-revenue">${formatCurrency(item.total_revenue)}</span>
            </div>
        `).join('');
    }

    function renderExtremeDays(extremes) {
        const bestDayEl = document.getElementById('kpi-best-day');
        const bestTotalEl = document.getElementById('kpi-best-total');
        const worstDayEl = document.getElementById('kpi-worst-day');
        const worstTotalEl = document.getElementById('kpi-worst-total');

        if (bestDayEl && extremes.best_day) {
            const bestDate = new Date(extremes.best_day);
            bestDayEl.textContent = bestDate.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
        } else if (bestDayEl) {
            bestDayEl.textContent = '--';
        }

        if (bestTotalEl) {
            bestTotalEl.textContent = formatCurrency(extremes.best_total || 0);
        }

        if (worstDayEl && extremes.worst_day) {
            const worstDate = new Date(extremes.worst_day);
            worstDayEl.textContent = worstDate.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
        } else if (worstDayEl) {
            worstDayEl.textContent = '--';
        }

        if (worstTotalEl) {
            worstTotalEl.textContent = formatCurrency(extremes.worst_total || 0);
        }
    }

    // Weekday chart
    let weekdayChart = null;
    const weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    function initWeekdayChart(data) {
        const canvas = document.getElementById('weekdayChart');
        if (!canvas) return;

        const ctx2 = canvas.getContext('2d');

        // Process data - SQL DAYOFWEEK returns 1=Sunday, 7=Saturday
        const salesByDay = [0, 0, 0, 0, 0, 0, 0];
        data.forEach(d => {
            const dayIndex = parseInt(d.weekday) - 1; // Convert 1-7 to 0-6
            if (dayIndex >= 0 && dayIndex < 7) {
                salesByDay[dayIndex] = parseFloat(d.total_sales);
            }
        });

        weekdayChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: weekdayLabels,
                datasets: [{
                    label: woospeed_dashboard_vars.i18n.revenue,
                    data: salesByDay,
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.7)',   // Sun - Red
                        'rgba(99, 102, 241, 0.7)',  // Mon - Indigo
                        'rgba(16, 185, 129, 0.7)', // Tue - Green
                        'rgba(245, 158, 11, 0.7)', // Wed - Amber
                        'rgba(236, 72, 153, 0.7)', // Thu - Pink
                        'rgba(59, 130, 246, 0.7)', // Fri - Blue
                        'rgba(139, 92, 246, 0.7)'  // Sat - Purple
                    ],
                    borderColor: [
                        '#ef4444', '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#8b5cf6'
                    ],
                    borderWidth: 2,
                    borderRadius: 6
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

    function updateWeekdayChart(data) {
        if (!weekdayChart) {
            initWeekdayChart(data);
            return;
        }

        const salesByDay = [0, 0, 0, 0, 0, 0, 0];
        data.forEach(d => {
            const dayIndex = parseInt(d.weekday) - 1;
            if (dayIndex >= 0 && dayIndex < 7) {
                salesByDay[dayIndex] = parseFloat(d.total_sales);
            }
        });

        weekdayChart.data.datasets[0].data = salesByDay;
        weekdayChart.update('none');
    }

    function loadDashboard() {
        const startTime = performance.now();

        // Build URL with date range
        let url = ajaxurl + '?action=woospeed_get_data&security=' + woospeed_dashboard_vars.nonce;

        if (startDate && endDate) {
            url += '&start_date=' + formatDate(startDate);
            url += '&end_date=' + formatDate(endDate);
        }

        fetch(url)
            .then(res => res.json())
            .then(response => {
                if (!response.success) return;
                const {
                    kpis,
                    chart,
                    leaderboard,
                    weekday_sales,
                    extreme_days,
                    bottom_products,
                    top_categories
                } = response.data;

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

                // v3.0 - Extreme Days
                if (extreme_days) {
                    renderExtremeDays(extreme_days);
                }

                // v3.0 - Weekday Chart
                if (weekday_sales) {
                    updateWeekdayChart(weekday_sales);
                }

                // Leaderboard
                renderLeaderboard(leaderboard);

                // v3.0 - Bottom Products
                if (bottom_products) {
                    renderBottomProducts(bottom_products);
                }

                // v3.0 - Top Categories
                if (top_categories) {
                    renderCategories(top_categories);
                }

                // Query Time
                const elapsed = ((performance.now() - startTime) / 1000).toFixed(3);
                const timeEl = document.getElementById('ws-query-time');
                if (timeEl) timeEl.textContent = woospeed_dashboard_vars.i18n.load_time + ': ' + elapsed + 's';
            })
            .catch(err => console.error('Dashboard error:', err));
    }

    // ========================================
    // Initialization
    // ========================================
    initDatePicker();
    loadDashboard();

    // Auto-refresh every 30s (increased from 10s for better UX)
    setInterval(loadDashboard, 30000);
});
