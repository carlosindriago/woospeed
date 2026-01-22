<div class="woospeed-dashboard">
    <div class="ws-header">
        <h1>
            <?php _e('Performance Overview', 'woospeed-analytics'); ?>
        </h1>
        <select id="ws-date-range" class="ws-date-select">
            <option value="7">
                <?php _e('Last 7 Days', 'woospeed-analytics'); ?>
            </option>
            <option value="30" selected>
                <?php _e('Last 30 Days', 'woospeed-analytics'); ?>
            </option>
            <option value="90">
                <?php _e('Last Quarter', 'woospeed-analytics'); ?>
            </option>
            <option value="365">
                <?php _e('This Year', 'woospeed-analytics'); ?>
            </option>
        </select>
    </div>

    <div class="ws-kpi-grid">
        <div class="ws-card revenue">
            <div class="ws-card-inner">
                <div class="ws-card-icon revenue">💰</div>
                <div class="ws-card-content">
                    <h3>
                        <?php _e('Total Revenue', 'woospeed-analytics'); ?>
                    </h3>
                    <p class="ws-value" id="kpi-revenue">$0.00</p>
                </div>
            </div>
        </div>
        <div class="ws-card orders">
            <div class="ws-card-inner">
                <div class="ws-card-icon orders">📦</div>
                <div class="ws-card-content">
                    <h3>
                        <?php _e('Orders', 'woospeed-analytics'); ?>
                    </h3>
                    <p class="ws-value" id="kpi-orders">0</p>
                </div>
            </div>
        </div>
        <div class="ws-card aov">
            <div class="ws-card-inner">
                <div class="ws-card-icon aov">📈</div>
                <div class="ws-card-content">
                    <h3>
                        <?php _e('Avg Order Value', 'woospeed-analytics'); ?>
                    </h3>
                    <p class="ws-value" id="kpi-aov">$0.00</p>
                </div>
            </div>
        </div>
        <div class="ws-card max">
            <div class="ws-card-inner">
                <div class="ws-card-icon max">🏆</div>
                <div class="ws-card-content">
                    <h3>
                        <?php _e('Max Order', 'woospeed-analytics'); ?>
                    </h3>
                    <p class="ws-value" id="kpi-max">$0.00</p>
                </div>
            </div>
        </div>
    </div>

    <div class="ws-main-grid">
        <div class="ws-card ws-chart-container">
            <h3
                style="margin-bottom:16px; font-size:16px; color:var(--ws-gray-900); text-transform:none; letter-spacing:0;">
                📈
                <?php _e('Sales Trend', 'woospeed-analytics'); ?>
            </h3>
            <canvas id="speedChart"></canvas>
        </div>

        <div class="ws-card ws-leaderboard">
            <h3>🏆
                <?php _e('Top Products', 'woospeed-analytics'); ?>
            </h3>
            <div id="leaderboard-container">
                <div class="ws-loading">
                    <?php _e('Loading...', 'woospeed-analytics'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="ws-status-bar">
        <span>⚡
            <?php _e('Engine', 'woospeed-analytics'); ?>: <strong>
                <?php _e('Flat Table + Raw SQL', 'woospeed-analytics'); ?>
            </strong>
        </span>
        <span id="ws-query-time">
            <?php _e('Load Time', 'woospeed-analytics'); ?>: --
        </span>
    </div>
</div>