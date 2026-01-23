<div class="woospeed-dashboard">
    <div class="ws-header">
        <h1>
            <?php _e('Performance Overview', 'woospeed-analytics'); ?>
        </h1>

        <!-- Advanced Date Range Picker -->
        <div class="ws-date-picker">
            <button type="button" id="ws-date-trigger" class="ws-date-trigger">
                <span class="ws-date-label"
                    id="ws-date-label"><?php _e('Month to date', 'woospeed-analytics'); ?></span>
                <span class="ws-date-range" id="ws-date-range-text"></span>
                <span class="ws-date-arrow">▼</span>
            </button>

            <div class="ws-date-dropdown" id="ws-date-dropdown">
                <div class="ws-date-dropdown-header">
                    <?php _e('Select a date range', 'woospeed-analytics'); ?>
                </div>

                <!-- Tabs -->
                <div class="ws-date-tabs">
                    <button type="button" class="ws-date-tab active"
                        data-tab="presets"><?php _e('Presets', 'woospeed-analytics'); ?></button>
                    <button type="button" class="ws-date-tab"
                        data-tab="custom"><?php _e('Custom', 'woospeed-analytics'); ?></button>
                </div>

                <!-- Presets Panel -->
                <div class="ws-date-panel" id="ws-panel-presets">
                    <div class="ws-presets-grid">
                        <button type="button" class="ws-preset-btn"
                            data-preset="today"><?php _e('Today', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="yesterday"><?php _e('Yesterday', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="week_to_date"><?php _e('Week to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="last_week"><?php _e('Last week', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn active"
                            data-preset="month_to_date"><?php _e('Month to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="last_month"><?php _e('Last month', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="quarter_to_date"><?php _e('Quarter to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="last_quarter"><?php _e('Last quarter', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="year_to_date"><?php _e('Year to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="ws-preset-btn"
                            data-preset="last_year"><?php _e('Last year', 'woospeed-analytics'); ?></button>
                    </div>
                </div>

                <!-- Custom Panel -->
                <div class="ws-date-panel" id="ws-panel-custom" style="display:none;">
                    <div class="ws-custom-dates">
                        <div class="ws-custom-field">
                            <label><?php _e('Start date', 'woospeed-analytics'); ?></label>
                            <input type="date" id="ws-custom-start" class="ws-custom-input">
                        </div>
                        <div class="ws-custom-field">
                            <label><?php _e('End date', 'woospeed-analytics'); ?></label>
                            <input type="date" id="ws-custom-end" class="ws-custom-input">
                        </div>
                    </div>
                </div>

                <!-- Compare Section -->
                <div class="ws-compare-section">
                    <div class="ws-compare-header"><?php _e('Compare to', 'woospeed-analytics'); ?></div>
                    <div class="ws-compare-options">
                        <label class="ws-compare-option">
                            <input type="radio" name="ws-compare" value="previous_period" checked>
                            <span><?php _e('Previous period', 'woospeed-analytics'); ?></span>
                        </label>
                        <label class="ws-compare-option">
                            <input type="radio" name="ws-compare" value="previous_year">
                            <span><?php _e('Previous year', 'woospeed-analytics'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- Update Button -->
                <div class="ws-date-actions">
                    <button type="button" id="ws-date-update"
                        class="ws-btn-primary"><?php _e('Update', 'woospeed-analytics'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="ws-kpi-grid">
        <div class="ws-card revenue">
            <div class="ws-card-inner">
                <div class="ws-card-icon revenue">💰</div>
                <div class="ws-card-content">
                    <h3><?php _e('Total Revenue', 'woospeed-analytics'); ?></h3>
                    <p class="ws-value" id="kpi-revenue">$0.00</p>
                </div>
            </div>
        </div>
        <div class="ws-card orders">
            <div class="ws-card-inner">
                <div class="ws-card-icon orders">📦</div>
                <div class="ws-card-content">
                    <h3><?php _e('Orders', 'woospeed-analytics'); ?></h3>
                    <p class="ws-value" id="kpi-orders">0</p>
                </div>
            </div>
        </div>
        <div class="ws-card aov">
            <div class="ws-card-inner">
                <div class="ws-card-icon aov">📈</div>
                <div class="ws-card-content">
                    <h3><?php _e('Avg Order Value', 'woospeed-analytics'); ?></h3>
                    <p class="ws-value" id="kpi-aov">$0.00</p>
                </div>
            </div>
        </div>
        <div class="ws-card max">
            <div class="ws-card-inner">
                <div class="ws-card-icon max">🏆</div>
                <div class="ws-card-content">
                    <h3><?php _e('Max Order', 'woospeed-analytics'); ?></h3>
                    <p class="ws-value" id="kpi-max">$0.00</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Best/Worst Day Cards -->
    <div class="ws-extremes-grid">
        <div class="ws-card ws-best-day">
            <div class="ws-card-inner">
                <div class="ws-card-icon best">🚀</div>
                <div class="ws-card-content">
                    <h3><?php _e('Best Sales Day', 'woospeed-analytics'); ?></h3>
                    <p class="ws-value" id="kpi-best-day">--</p>
                    <p class="ws-subvalue" id="kpi-best-total">$0.00</p>
                </div>
            </div>
        </div>
        <div class="ws-card ws-worst-day">
            <div class="ws-card-inner">
                <div class="ws-card-icon worst">📉</div>
                <div class="ws-card-content">
                    <h3><?php _e('Lowest Sales Day', 'woospeed-analytics'); ?></h3>
                    <p class="ws-value" id="kpi-worst-day">--</p>
                    <p class="ws-subvalue" id="kpi-worst-total">$0.00</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="ws-main-grid">
        <div class="ws-card ws-chart-container">
            <h3 class="ws-section-title">
                📈 <?php _e('Sales Trend', 'woospeed-analytics'); ?>
            </h3>
            <canvas id="speedChart"></canvas>
        </div>

        <div class="ws-card ws-chart-container">
            <h3 class="ws-section-title">
                📊 <?php _e('Sales by Day of Week', 'woospeed-analytics'); ?>
            </h3>
            <canvas id="weekdayChart"></canvas>
        </div>
    </div>

    <!-- Leaderboards Row -->
    <div class="ws-leaderboards-grid">
        <div class="ws-card ws-leaderboard">
            <h3>🏆 <?php _e('Top Products', 'woospeed-analytics'); ?></h3>
            <div id="leaderboard-container">
                <div class="ws-loading"><?php _e('Loading...', 'woospeed-analytics'); ?></div>
            </div>
        </div>

        <div class="ws-card ws-leaderboard">
            <h3>⬇️ <?php _e('Least Sold Products', 'woospeed-analytics'); ?></h3>
            <div id="bottom-products-container">
                <div class="ws-loading"><?php _e('Loading...', 'woospeed-analytics'); ?></div>
            </div>
        </div>

        <div class="ws-card ws-leaderboard">
            <h3>📁 <?php _e('Top Categories', 'woospeed-analytics'); ?></h3>
            <div id="categories-container">
                <div class="ws-loading"><?php _e('Loading...', 'woospeed-analytics'); ?></div>
            </div>
        </div>
    </div>

    <div class="ws-status-bar">
        <span>⚡ <?php _e('Engine', 'woospeed-analytics'); ?>:
            <strong><?php _e('Flat Table + Raw SQL', 'woospeed-analytics'); ?></strong></span>
        <span id="ws-query-time"><?php _e('Load Time', 'woospeed-analytics'); ?>: --</span>
    </div>
</div>