<div class="woospeed-dashboard">
    <div class="woospeed-header">
        <h1>
            <?php _e('Performance Overview', 'woospeed-analytics'); ?>
        </h1>

        <!-- Advanced Date Range Picker -->
        <div class="woospeed-date-picker">
            <button type="button" id="woospeed-date-trigger" class="woospeed-date-trigger">
                <span class="woospeed-date-label"
                    id="woospeed-date-label"><?php _e('Month to date', 'woospeed-analytics'); ?></span>
                <span class="woospeed-date-range" id="woospeed-date-range-text"></span>
                <span class="woospeed-date-arrow">▼</span>
            </button>

            <div class="woospeed-date-dropdown" id="woospeed-date-dropdown">
                <div class="woospeed-date-dropdown-header">
                    <?php _e('Select a date range', 'woospeed-analytics'); ?>
                </div>

                <!-- Tabs -->
                <div class="woospeed-date-tabs">
                    <button type="button" class="woospeed-date-tab active"
                        data-tab="presets"><?php _e('Presets', 'woospeed-analytics'); ?></button>
                    <button type="button" class="woospeed-date-tab"
                        data-tab="custom"><?php _e('Custom', 'woospeed-analytics'); ?></button>
                </div>

                <!-- Presets Panel -->
                <div class="woospeed-date-panel" id="woospeed-panel-presets">
                    <div class="woospeed-presets-grid">
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="today"><?php _e('Today', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="yesterday"><?php _e('Yesterday', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="week_to_date"><?php _e('Week to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="last_week"><?php _e('Last week', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn active"
                            data-preset="month_to_date"><?php _e('Month to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="last_month"><?php _e('Last month', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="quarter_to_date"><?php _e('Quarter to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="last_quarter"><?php _e('Last quarter', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="year_to_date"><?php _e('Year to date', 'woospeed-analytics'); ?></button>
                        <button type="button" class="woospeed-preset-btn"
                            data-preset="last_year"><?php _e('Last year', 'woospeed-analytics'); ?></button>
                    </div>
                </div>

                <!-- Custom Panel -->
                <div class="woospeed-date-panel" id="woospeed-panel-custom" style="display:none;">
                    <div class="woospeed-custom-dates">
                        <div class="woospeed-custom-field">
                            <label><?php _e('Start date', 'woospeed-analytics'); ?></label>
                            <input type="date" id="woospeed-custom-start" class="woospeed-custom-input">
                        </div>
                        <div class="woospeed-custom-field">
                            <label><?php _e('End date', 'woospeed-analytics'); ?></label>
                            <input type="date" id="woospeed-custom-end" class="woospeed-custom-input">
                        </div>
                    </div>
                </div>

                <!-- Compare Section -->
                <div class="woospeed-compare-section">
                    <div class="woospeed-compare-header"><?php _e('Compare to', 'woospeed-analytics'); ?></div>
                    <div class="woospeed-compare-options">
                        <label class="woospeed-compare-option">
                            <input type="radio" name="ws-compare" value="previous_period" checked>
                            <span><?php _e('Previous period', 'woospeed-analytics'); ?></span>
                        </label>
                        <label class="woospeed-compare-option">
                            <input type="radio" name="ws-compare" value="previous_year">
                            <span><?php _e('Previous year', 'woospeed-analytics'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- Update Button -->
                <div class="woospeed-date-actions">
                    <button type="button" id="woospeed-date-update"
                        class="woospeed-btn-primary"><?php _e('Update', 'woospeed-analytics'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="woospeed-kpi-grid">
        <div class="woospeed-card revenue">
            <div class="woospeed-card-inner">
                <div class="woospeed-card-icon revenue">💰</div>
                <div class="woospeed-card-content">
                    <h3><?php _e('Total Revenue', 'woospeed-analytics'); ?></h3>
                    <p class="woospeed-value" id="kpi-revenue">$0.00</p>
                </div>
            </div>
        </div>
        <div class="woospeed-card orders">
            <div class="woospeed-card-inner">
                <div class="woospeed-card-icon orders">📦</div>
                <div class="woospeed-card-content">
                    <h3><?php _e('Orders', 'woospeed-analytics'); ?></h3>
                    <p class="woospeed-value" id="kpi-orders">0</p>
                </div>
            </div>
        </div>
        <div class="woospeed-card aov">
            <div class="woospeed-card-inner">
                <div class="woospeed-card-icon aov">📈</div>
                <div class="woospeed-card-content">
                    <h3><?php _e('Avg Order Value', 'woospeed-analytics'); ?></h3>
                    <p class="woospeed-value" id="kpi-aov">$0.00</p>
                </div>
            </div>
        </div>
        <div class="woospeed-card max">
            <div class="woospeed-card-inner">
                <div class="woospeed-card-icon max">🏆</div>
                <div class="woospeed-card-content">
                    <h3><?php _e('Max Order', 'woospeed-analytics'); ?></h3>
                    <p class="woospeed-value" id="kpi-max">$0.00</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Best/Worst Day Cards -->
    <div class="woospeed-extremes-grid">
        <div class="woospeed-card woospeed-best-day">
            <div class="woospeed-card-inner">
                <div class="woospeed-card-icon best">🚀</div>
                <div class="woospeed-card-content">
                    <h3><?php _e('Best Sales Day', 'woospeed-analytics'); ?></h3>
                    <p class="woospeed-value" id="kpi-best-day">--</p>
                    <p class="woospeed-subvalue" id="kpi-best-total">$0.00</p>
                </div>
            </div>
        </div>
        <div class="woospeed-card woospeed-worst-day">
            <div class="woospeed-card-inner">
                <div class="woospeed-card-icon worst">📉</div>
                <div class="woospeed-card-content">
                    <h3><?php _e('Lowest Sales Day', 'woospeed-analytics'); ?></h3>
                    <p class="woospeed-value" id="kpi-worst-day">--</p>
                    <p class="woospeed-subvalue" id="kpi-worst-total">$0.00</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="woospeed-main-grid">
        <div class="woospeed-card woospeed-chart-container">
            <h3 class="woospeed-section-title">
                📈 <?php _e('Sales Trend', 'woospeed-analytics'); ?>
            </h3>
            <canvas id="speedChart"></canvas>
        </div>

        <div class="woospeed-card woospeed-chart-container">
            <h3 class="woospeed-section-title">
                📊 <?php _e('Sales by Day of Week', 'woospeed-analytics'); ?>
            </h3>
            <canvas id="weekdayChart"></canvas>
        </div>
    </div>

    <!-- Leaderboards Row -->
    <div class="woospeed-leaderboards-grid">
        <div class="woospeed-card woospeed-leaderboard">
            <h3>🏆 <?php _e('Top Products', 'woospeed-analytics'); ?></h3>
            <div id="leaderboard-container">
                <div class="woospeed-loading"><?php _e('Loading...', 'woospeed-analytics'); ?></div>
            </div>
        </div>

        <div class="woospeed-card woospeed-leaderboard">
            <h3>⬇️ <?php _e('Least Sold Products', 'woospeed-analytics'); ?></h3>
            <div id="bottom-products-container">
                <div class="woospeed-loading"><?php _e('Loading...', 'woospeed-analytics'); ?></div>
            </div>
        </div>

        <div class="woospeed-card woospeed-leaderboard">
            <h3>📁 <?php _e('Top Categories', 'woospeed-analytics'); ?></h3>
            <div id="categories-container">
                <div class="woospeed-loading"><?php _e('Loading...', 'woospeed-analytics'); ?></div>
            </div>
        </div>
    </div>

    <div class="woospeed-status-bar">
        <span>⚡ <?php _e('Engine', 'woospeed-analytics'); ?>:
            <strong><?php _e('Flat Table + Raw SQL', 'woospeed-analytics'); ?></strong></span>
        <span id="woospeed-query-time"><?php _e('Load Time', 'woospeed-analytics'); ?>: --</span>
    </div>
</div>