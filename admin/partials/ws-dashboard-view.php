<div class="woospeed-dashboard">
    <div class="ws-header">
        <h1>Performance Overview</h1>
        <select id="ws-date-range" class="ws-date-select">
            <option value="7">Últimos 7 días</option>
            <option value="30" selected>Últimos 30 días</option>
            <option value="90">Último Trimestre</option>
            <option value="365">Este Año</option>
        </select>
    </div>

    <div class="ws-kpi-grid">
        <div class="ws-card revenue">
            <div class="ws-card-inner">
                <div class="ws-card-icon revenue">💰</div>
                <div class="ws-card-content">
                    <h3>Ingresos Totales</h3>
                    <p class="ws-value" id="kpi-revenue">$0.00</p>
                </div>
            </div>
        </div>
        <div class="ws-card orders">
            <div class="ws-card-inner">
                <div class="ws-card-icon orders">📦</div>
                <div class="ws-card-content">
                    <h3>Pedidos</h3>
                    <p class="ws-value" id="kpi-orders">0</p>
                </div>
            </div>
        </div>
        <div class="ws-card aov">
            <div class="ws-card-inner">
                <div class="ws-card-icon aov">📈</div>
                <div class="ws-card-content">
                    <h3>Ticket Promedio</h3>
                    <p class="ws-value" id="kpi-aov">$0.00</p>
                </div>
            </div>
        </div>
        <div class="ws-card max">
            <div class="ws-card-inner">
                <div class="ws-card-icon max">🏆</div>
                <div class="ws-card-content">
                    <h3>Pedido Máximo</h3>
                    <p class="ws-value" id="kpi-max">$0.00</p>
                </div>
            </div>
        </div>
    </div>

    <div class="ws-main-grid">
        <div class="ws-card ws-chart-container">
            <h3
                style="margin-bottom:16px; font-size:16px; color:var(--ws-gray-900); text-transform:none; letter-spacing:0;">
                📈 Tendencia de Ventas</h3>
            <canvas id="speedChart"></canvas>
        </div>

        <div class="ws-card ws-leaderboard">
            <h3>🏆 Top Productos</h3>
            <div id="leaderboard-container">
                <div class="ws-loading">Cargando...</div>
            </div>
        </div>
    </div>

    <div class="ws-status-bar">
        <span>⚡ Motor: <strong>Tabla Plana + Raw SQL</strong></span>
        <span id="ws-query-time">Tiempo de carga: --</span>
    </div>
</div>