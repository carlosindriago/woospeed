<?php
// 🛡️ Crear Llave de Seguridad (Nonce)
$nonce = wp_create_nonce('woospeed_seed_nonce');
?>
<div class="wrap">
    <h1>🛠️ Generador de Datos Stress-Test</h1>
    <p>Utilice estas herramientas para simular actividad de alto tráfico en la tienda.</p>

    <?php if (isset($_GET['seeded'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                ✅ Operación Completada:
                Generados <b>
                    <?php echo esc_html($_GET['count']); ?>
                </b> items
                (Tipo:
                <?php echo esc_html($_GET['type']); ?>).
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cleared'])): ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                🧹 Limpieza Completada:
                Se han eliminado <b>
                    <?php echo esc_html($_GET['count']); ?>
                </b> registros de prueba.
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['migrated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                🔄 Migración Completada:
                Se han sincronizado <b>
                    <?php echo esc_html($_GET['count']); ?>
                </b> items de productos a la tabla de
                leaderboard.
            </p>
        </div>
    <?php endif; ?>

    <!-- Card de Migración (Si hay órdenes sin items) -->
    <?php
    global $wpdb;
    $table_reports = $wpdb->prefix . 'wc_speed_reports';
    $table_items = $wpdb->prefix . 'wc_speed_order_items';

    // Should properly use Repository methods here, but for partials raw SQL is sometimes cleaner if simple.
    // Better practice: Pass variables from Controller (Admin class).
    // For now, I'll keep the logic here to minimize refactor risk, but ideally $needs_migration should be passed in.
    
    $orders_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_reports");
    $items_count = $wpdb->get_var("SELECT COUNT(DISTINCT order_id) FROM $table_items");
    $needs_migration = ($orders_count > 0 && $items_count < $orders_count);
    ?>

    <?php if ($needs_migration): ?>
        <div
            style="background: #fff3cd; border: 1px solid #ffc107; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h3 style="margin-top:0; color: #856404;">⚠️ Migración Requerida</h3>
            <p>Tienes <b>
                    <?php echo number_format($orders_count); ?>
                </b> órdenes en el sistema, pero solo
                <b>
                    <?php echo number_format($items_count); ?>
                </b> tienen sus productos sincronizados para el "Top
                Productos".
            </p>
            <a href="<?php echo admin_url('admin.php?page=woospeed-generator&seed_action=migrate_items'); ?>"
                class="button button-primary"
                onclick="return confirm('Esto sincronizará los items de todas las órdenes existentes. ¿Continuar?');">
                🔄 Migrar Items Ahora
            </a>
            <p style="font-size: 11px; color: #856404; margin-top: 10px;">* Esto puede tardar unos segundos dependiendo del
                número de órdenes.</p>
        </div>
    <?php endif; ?>

    <!-- Barra de Progreso (Oculta por defecto) -->
    <div id="seed-progress-container"
        style="display:none; margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05);">
        <h3>⏳ Generando Datos Masivos...</h3>
        <p>Por favor no cierres esta pestaña. Procesando <span id="processed-count">0</span> de <span
                id="total-count">0</span>...</p>
        <progress id="seed-progress" value="0" max="100" style="width: 100%; height: 30px;"></progress>
    </div>

    <div style="display: flex; gap: 20px; margin-top: 20px;">
        <!-- Card 1: Productos (Paso 1) -->
        <div
            style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-top: 4px solid #F5B041;">
            <h3>1. Primero: Productos Dummy</h3>
            <p>Genera <b>20 Productos Reales</b>. Es <b>obligatorio</b> tener productos antes de simular ventas.</p>
            <a href="<?php echo admin_url('admin.php?page=woospeed-generator&seed_action=products_20'); ?>"
                class="button button-secondary" style="width:100%; margin-top:10px;"
                onclick="return confirm('¿Crear 20 Productos Reales?');">
                📦 Generar Productos (Paso 1)
            </a>
            <p style="font-size: 11px; color: #666; margin-top: 5px;">* Las órdenes usarán estos productos.</p>
        </div>

        <!-- Card 2: Masiva (Paso 2) -->
        <div
            style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-top: 4px solid #007cba;">
            <h3>2. Segundo: Carga Masiva (5k)</h3>
            <p>Genera <b>5,000 Pedidos Reales</b> usando los productos del paso 1. <br>Refleja una carga real.</p>
            <button id="btn-start-batch" class="button button-primary" style="width:100%; margin-top:10px;">
                🚀 Iniciar Carga Masiva (Paso 2)
            </button>
            <p style="font-size: 11px; color: #666; margin-top: 5px;">* Se ejecutará en 10 tandas de 500.</p>
        </div>

        <!-- Card 3 -->
        <div
            style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05);">
            <h3>3. Test Rápido (50)</h3>
            <p>Prueba rápida de sincronización con 50 pedidos (opcional).</p>
            <a href="<?php echo admin_url('admin.php?page=woospeed-generator&seed_action=orders_50'); ?>"
                class="button button-secondary" onclick="return confirm('¿Crear 50 Pedidos Reales?');">
                🛒 Generar 50 Pedidos
            </a>
        </div>
    </div>



    <div style="margin-top: 20px;">
        <p><i>Nota: Las órdenes reales aparecerán en "WooCommerce > Pedidos" y se sincronizarán automáticamente con el
                Dashboard de Speed Analytics.</i></p>
    </div>

    <!-- Danger Zone -->
    <div
        style="margin-top: 30px; background: #fff; padding: 20px; border: 1px solid #dc3232; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-left-width: 4px;">
        <h3 style="color: #dc3232; margin-top:0;">⚠️ Zona de Limpieza</h3>
        <p>Borra TODOS los datos generados por este plugin (Tabla Plana, Productos Dummy, Órdenes Dummy).</p>
        <a href="<?php echo admin_url('admin.php?page=woospeed-generator&seed_action=clear_all'); ?>"
            class="button button-link-delete"
            style="color: #a00; text-decoration: none; border: 1px solid #dc3232; padding: 5px 10px; border-radius: 3px;"
            onclick="return confirm('¿ESTÁS SEGURO? Esto borrará todos los datos de prueba generados.');">
            🗑️ BORRAR TODOS LOS DATOS DUMMY
        </a>
    </div>
</div>