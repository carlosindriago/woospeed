/**
 * WooSpeed Analytics Dashboard - Modular Class
 *
 * Handles dashboard functionality with ES6 modules, private fields,
 * and encapsulated state management.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

class WooSpeedDashboard {
    // ========================================
    // Private Fields
    // ========================================

    #chartCanvas = null;
    #ctx = null;
    #speedChart = null;
    #weekdayChart = null;

    // Date Range State
    #currentPreset = 'month_to_date';
    #startDate = null;
    #endDate = null;
    #compareType = 'previous_period';

    // Chart Instances
    #weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Constants
    #AUTO_REFRESH_INTERVAL = 30000; // 30 seconds

    // ========================================
    // Constructor
    // ========================================

    constructor() {
        this.#init();
    }

    // ========================================
    // Initialization
    // ========================================

    #init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.#setup());
        } else {
            this.#setup();
        }
    }

    #setup() {
        // Initialize canvas elements
        this.#chartCanvas = document.getElementById('speedChart');

        if (!this.#chartCanvas) {
            console.warn('[WooSpeed] Dashboard canvas not found');
            return;
        }

        this.#ctx = this.#chartCanvas.getContext('2d');

        // Initialize date picker
        this.#initDatePicker();

        // Load initial data
        this.#loadDashboard();

        // Set up auto-refresh
        setInterval(() => this.#loadDashboard(), this.#AUTO_REFRESH_INTERVAL);

        console.log('[WooSpeed] Dashboard initialized');
    }

    // ========================================
    // Date Range Methods
    // ========================================

    #initDatePicker() {
        const trigger = document.getElementById('ws-date-trigger');
        const dropdown = document.getElementById('ws-date-dropdown');

        if (!trigger || !dropdown) return;

        // Toggle dropdown
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            trigger.classList.toggle('active');
            dropdown.classList.toggle('open');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.ws-date-picker')) {
                trigger.classList.remove('active');
                dropdown.classList.remove('open');
            }
        });

        // Tab switching
        this.#setupDatePickerTabs();

        // Preset buttons
        this.#setupPresetButtons();

        // Compare option
        this.#setupCompareOptions();

        // Update button
        this.#setupUpdateButton(trigger, dropdown);

        // Set initial dates
        const initialDates = this.#calculatePresetDates(this.#currentPreset);
        this.#startDate = initialDates.start;
        this.#endDate = initialDates.end;

        // Update UI
        this.#updateDatePickerUI();
    }

    #setupDatePickerTabs() {
        const tabs = document.querySelectorAll('.ws-date-tab');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const tabName = tab.dataset.tab;
                document.getElementById('ws-panel-presets').style.display =
                    tabName === 'presets' ? 'block' : 'none';
                document.getElementById('ws-panel-custom').style.display =
                    tabName === 'custom' ? 'block' : 'none';
            });
        });
    }

    #setupPresetButtons() {
        const presetBtns = document.querySelectorAll('.ws-preset-btn');
        const customStart = document.getElementById('ws-custom-start');
        const customEnd = document.getElementById('ws-custom-end');

        presetBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                presetBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                this.#currentPreset = btn.dataset.preset;

                const dates = this.#calculatePresetDates(this.#currentPreset);
                this.#startDate = dates.start;
                this.#endDate = dates.end;

                // Update custom inputs to match
                if (customStart && customEnd) {
                    customStart.value = this.#formatDate(this.#startDate);
                    customEnd.value = this.#formatDate(this.#endDate);
                }
            });
        });
    }

    #setupCompareOptions() {
        const radios = document.querySelectorAll('input[name="ws-compare"]');

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                this.#compareType = radio.value;
            });
        });
    }

    #setupUpdateButton(trigger, dropdown) {
        const updateBtn = document.getElementById('ws-date-update');

        if (!updateBtn) return;

        updateBtn.addEventListener('click', () => {
            // Check if custom tab is active
            const customPanel = document.getElementById('ws-panel-custom');

            if (customPanel && customPanel.style.display !== 'none') {
                const customStart = document.getElementById('ws-custom-start');
                const customEnd = document.getElementById('ws-custom-end');

                if (customStart && customEnd && customStart.value && customEnd.value) {
                    this.#startDate = new Date(customStart.value);
                    this.#endDate = new Date(customEnd.value);
                    this.#currentPreset = 'custom';
                }
            }

            // Close dropdown
            trigger.classList.remove('active');
            dropdown.classList.remove('open');

            // Update UI and reload
            this.#updateDatePickerUI();
            this.#loadDashboard();
        });
    }

    // ========================================
    // Date Calculation Methods
    // ========================================

    #calculatePresetDates(preset) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        let start;
        let end;

        switch (preset) {
            case 'today':
                start = new Date(today);
                end = new Date(today);
                break;

            case 'yesterday':
                start = new Date(today);
                start.setDate(start.getDate() - 1);
                end = new Date(start);
                break;

            case 'week_to_date':
                start = new Date(today);
                const dayOfWeek = start.getDay();
                const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
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

    #formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    #formatDateRange(start, end) {
        const options = { month: 'short', day: 'numeric' };
        const startStr = start.toLocaleDateString('en-US', options);
        const endStr = end.toLocaleDateString('en-US', { ...options, year: 'numeric' });

        return `(${startStr} - ${endStr})`;
    }

    #updateDatePickerUI() {
        const labelEl = document.getElementById('ws-date-label');
        const rangeTextEl = document.getElementById('ws-date-range-text');

        if (labelEl && woospeed_dashboard_vars.i18n.presets) {
            labelEl.textContent = woospeed_dashboard_vars.i18n.presets[this.#currentPreset] || this.#currentPreset;
        }

        if (rangeTextEl && this.#startDate && this.#endDate) {
            rangeTextEl.textContent = this.#formatDateRange(this.#startDate, this.#endDate);
        }
    }

    // ========================================
    // Dashboard Data Methods
    // ========================================

    async #loadDashboard() {
        const startTime = performance.now();

        try {
            // Build URL with date range
            const url = this.#buildApiUrl();

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.data?.message || 'Unknown error');
            }

            // Extract data
            const {
                kpis,
                chart,
                leaderboard,
                weekday_sales,
                extreme_days,
                bottom_products,
                top_categories
            } = data.data;

            // Update UI components
            this.#updateKPIs(kpis);
            this.#updateChart(chart);
            this.#updateWeekdayChart(weekday_sales);
            this.#updateExtremeDays(extreme_days);
            this.#updateLeaderboard(leaderboard);
            this.#updateBottomProducts(bottom_products);
            this.#updateCategories(top_categories);

            // Update query time
            const elapsed = ((performance.now() - startTime) / 1000).toFixed(3);
            this.#updateQueryTime(elapsed);

        } catch (error) {
            console.error('[WooSpeed] Dashboard error:', error);
            this.#showError('Failed to load dashboard data');
        }
    }

    #buildApiUrl() {
        let url = ajaxurl + '?action=woospeed_get_data&security=' + woospeed_dashboard_vars.nonce;

        if (this.#startDate && this.#endDate) {
            url += '&start_date=' + this.#formatDate(this.#startDate);
            url += '&end_date=' + this.#formatDate(this.#endDate);
        }

        return url;
    }

    // ========================================
    // UI Update Methods
    // ========================================

    #updateKPIs(kpis) {
        const revenueEl = document.getElementById('kpi-revenue');
        const ordersEl = document.getElementById('kpi-orders');
        const aovEl = document.getElementById('kpi-aov');
        const maxEl = document.getElementById('kpi-max');

        if (revenueEl) revenueEl.textContent = this.#formatCurrency(kpis.revenue);
        if (ordersEl) ordersEl.textContent = kpis.orders.toLocaleString();
        if (aovEl) aovEl.textContent = this.#formatCurrency(kpis.aov);
        if (maxEl) maxEl.textContent = this.#formatCurrency(kpis.max_order);
    }

    #updateChart(chart) {
        if (!this.#speedChart) {
            this.#initChart(chart);
        } else {
            this.#speedChart.data.labels = chart.map(d => d.report_date);
            this.#speedChart.data.datasets[0].data = chart.map(d => parseFloat(d.total_sales));
            this.#speedChart.update('none');
        }
    }

    #initChart(data) {
        this.#speedChart = new Chart(this.#ctx, {
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

    #updateWeekdayChart(data) {
        if (!this.#weekdayChart) {
            this.#initWeekdayChart(data);
        } else {
            const salesByDay = this.#processWeekdayData(data);
            this.#weekdayChart.data.datasets[0].data = salesByDay;
            this.#weekdayChart.update('none');
        }
    }

    #initWeekdayChart(data) {
        const canvas = document.getElementById('weekdayChart');
        if (!canvas) return;

        const ctx2 = canvas.getContext('2d');
        const salesByDay = this.#processWeekdayData(data);

        this.#weekdayChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: this.#weekdayLabels,
                datasets: [{
                    label: woospeed_dashboard_vars.i18n.revenue,
                    data: salesByDay,
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(139, 92, 246, 0.7)'
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

    #processWeekdayData(data) {
        const salesByDay = [0, 0, 0, 0, 0, 0, 0];

        data.forEach(d => {
            const dayIndex = parseInt(d.weekday) - 1;
            if (dayIndex >= 0 && dayIndex < 7) {
                salesByDay[dayIndex] = parseFloat(d.total_sales);
            }
        });

        return salesByDay;
    }

    #updateExtremeDays(extremes) {
        const bestDayEl = document.getElementById('kpi-best-day');
        const bestTotalEl = document.getElementById('kpi-best-total');
        const worstDayEl = document.getElementById('kpi-worst-day');
        const worstTotalEl = document.getElementById('kpi-worst-total');

        if (bestDayEl && extremes.best_day) {
            const bestDate = new Date(extremes.best_day);
            bestDayEl.textContent = bestDate.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'short',
                day: 'numeric'
            });
        } else if (bestDayEl) {
            bestDayEl.textContent = '--';
        }

        if (bestTotalEl) {
            bestTotalEl.textContent = this.#formatCurrency(extremes.best_total || 0);
        }

        if (worstDayEl && extremes.worst_day) {
            const worstDate = new Date(extremes.worst_day);
            worstDayEl.textContent = worstDate.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'short',
                day: 'numeric'
            });
        } else if (worstDayEl) {
            worstDayEl.textContent = '--';
        }

        if (worstTotalEl) {
            worstTotalEl.textContent = this.#formatCurrency(extremes.worst_total || 0);
        }
    }

    #updateLeaderboard(items) {
        const container = document.getElementById('leaderboard-container');

        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = this.#renderEmptyState('📦');
            return;
        }

        container.innerHTML = items.map((item, i) => `
            <div class="ws-leaderboard-item">
                <span class="ws-leaderboard-rank ${i === 0 ? 'gold' : ''}">${i + 1}</span>
                <span class="ws-leaderboard-name">${this.#escapeHtml(item.product_name)}</span>
                <span class="ws-leaderboard-sold">${item.total_sold} ${woospeed_dashboard_vars.i18n.sold}</span>
            </div>
        `).join('');
    }

    #updateBottomProducts(items) {
        const container = document.getElementById('bottom-products-container');

        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = this.#renderEmptyState('📊');
            return;
        }

        container.innerHTML = items.map((item, i) => `
            <div class="ws-bottom-item">
                <span class="ws-bottom-rank">${i + 1}</span>
                <span class="ws-bottom-name">${this.#escapeHtml(item.product_name)}</span>
                <span class="ws-bottom-sold">${item.total_sold} ${woospeed_dashboard_vars.i18n.sold}</span>
            </div>
        `).join('');
    }

    #updateCategories(items) {
        const container = document.getElementById('categories-container');

        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = this.#renderEmptyState('📁');
            return;
        }

        container.innerHTML = items.map(item => `
            <div class="ws-category-item">
                <span class="ws-category-name">${this.#escapeHtml(item.category_name)}</span>
                <span class="ws-category-revenue">${this.#formatCurrency(item.total_revenue)}</span>
            </div>
        `).join('');
    }

    #updateQueryTime(elapsed) {
        const timeEl = document.getElementById('ws-query-time');

        if (timeEl) {
            timeEl.textContent = woospeed_dashboard_vars.i18n.load_time + ': ' + elapsed + 's';
        }
    }

    // ========================================
    // Helper Methods
    // ========================================

    #formatCurrency(value) {
        return '$' + parseFloat(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    #escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    #renderEmptyState(icon) {
        return `
            <div class="ws-empty-state">
                <div class="ws-empty-state-icon">${icon}</div>
                <div class="ws-empty-state-text">${woospeed_dashboard_vars.i18n.no_data}</div>
            </div>
        `;
    }

    #showError(message) {
        console.error('[WooSpeed]', message);
        // Could implement user-facing error UI here
    }
}

// ========================================
// Initialize
// ========================================
new WooSpeedDashboard();
