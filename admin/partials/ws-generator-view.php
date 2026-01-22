<?php
// 🛡️ Crear Llave de Seguridad (Nonce)
$nonce = wp_create_nonce('woospeed_seed_nonce');
?>
<div class="wrap">
    <h1><?php _e('🛠️ Stress-Test Data Generator', 'woospeed-analytics'); ?></h1>
    <p><?php _e('Use these tools to simulate high-traffic activity on the store.', 'woospeed-analytics'); ?></p>

    <?php if (isset($_GET['seeded'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php _e('✅ Operation Complete:', 'woospeed-analytics'); ?>
                <?php printf(__('Generated <b>%s</b> items', 'woospeed-analytics'), esc_html($_GET['count'])); ?>
                (<?php printf(__('Type: %s', 'woospeed-analytics'), esc_html($_GET['type'])); ?>).
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cleared'])): ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php _e('🧹 Cleanup Complete:', 'woospeed-analytics'); ?>
                <?php printf(__('Deleted <b>%s</b> test records.', 'woospeed-analytics'), esc_html($_GET['count'])); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['migrated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php _e('🔄 Migration Complete:', 'woospeed-analytics'); ?>
                <?php printf(__('Synchronized <b>%s</b> items to leaderboard table.', 'woospeed-analytics'), esc_html($_GET['count'])); ?>
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
            <h3 style="margin-top:0; color: #856404;"><?php _e('⚠️ Migration Required', 'woospeed-analytics'); ?></h3>
            <p>
                <?php printf(
                    __('You have <b>%s</b> orders in the system, but only <b>%s</b> have their products synchronized for "Top Products".', 'woospeed-analytics'),
                    number_format($orders_count),
                    number_format($items_count)
                ); ?>
            </p>
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=woospeed-generator&seed_action=migrate_items'), 'woospeed_seed_action'); ?>"
                class="button button-primary"
                onclick="return confirm('<?php _e('This will synchronize items for all existing orders. Continue?', 'woospeed-analytics'); ?>');">
                <?php _e('🔄 Migrate Items Now', 'woospeed-analytics'); ?>
            </a>
            <p style="font-size: 11px; color: #856404; margin-top: 10px;">*
                <?php _e('This may take a few seconds depending on the number of orders.', 'woospeed-analytics'); ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Barra de Progreso (Oculta por defecto) -->
    <div id="seed-progress-container"
        style="display:none; margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05);">
        <h3><?php _e('⏳ Generating Mass Data...', 'woospeed-analytics'); ?></h3>
        <p><?php _e('Please do not close this tab. Processing', 'woospeed-analytics'); ?> <span
                id="processed-count">0</span> <?php _e('of', 'woospeed-analytics'); ?> <span
                id="total-count">0</span>...</p>
        <progress id="seed-progress" value="0" max="100" style="width: 100%; height: 30px;"></progress>
    </div>

    <div style="display: flex; gap: 20px; margin-top: 20px;">
        <!-- Card 1: Productos (Paso 1) -->
        <div
            style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-top: 4px solid #F5B041;">
            <h3><?php _e('1. First: Dummy Products', 'woospeed-analytics'); ?></h3>
            <p><?php _e('Generates <b>20 Real Products</b>. It is <b>mandatory</b> to have products before simulating sales.', 'woospeed-analytics'); ?>
            </p>
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=woospeed-generator&seed_action=products_20'), 'woospeed_seed_action'); ?>"
                class="button button-secondary" style="width:100%; margin-top:10px;"
                onclick="return confirm('<?php _e('Create 20 Dummy Products?', 'woospeed-analytics'); ?>');">
                <?php _e('📦 Generate Products (Step 1)', 'woospeed-analytics'); ?>
            </a>
            <p style="font-size: 11px; color: #666; margin-top: 5px;">*
                <?php _e('Orders will use these products.', 'woospeed-analytics'); ?>
            </p>
        </div>

        <!-- Card 2: Masiva (Paso 2) -->
        <div
            style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-top: 4px solid #007cba;">
            <h3><?php _e('2. Second: Mass Load (5k)', 'woospeed-analytics'); ?></h3>
            <p><?php _e('Generates <b>5,000 Real Orders</b> using products from step 1.', 'woospeed-analytics'); ?>
                <br><?php _e('Reflects real load.', 'woospeed-analytics'); ?>
            </p>
            <button id="btn-start-batch" class="button button-primary" style="width:100%; margin-top:10px;">
                <?php _e('🚀 Start Mass Load (Step 2)', 'woospeed-analytics'); ?>
            </button>
            <p style="font-size: 11px; color: #666; margin-top: 5px;">*
                <?php _e('Will run in 10 batches of 500.', 'woospeed-analytics'); ?>
            </p>
        </div>

        <!-- Card 3 -->
        <div
            style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05);">
            <h3><?php _e('3. Quick Test (50)', 'woospeed-analytics'); ?></h3>
            <p><?php _e('Quick synchronization test with 50 orders (optional).', 'woospeed-analytics'); ?></p>
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=woospeed-generator&seed_action=orders_50'), 'woospeed_seed_action'); ?>"
                class="button button-secondary"
                onclick="return confirm('<?php _e('Create 50 Real Orders?', 'woospeed-analytics'); ?>');">
                <?php _e('🛒 Generate 50 Orders', 'woospeed-analytics'); ?>
            </a>
        </div>
    </div>


    <div style="margin-top: 20px;">
        <p><i><?php _e('Note: Real orders will appear in "WooCommerce > Orders" and automatically sync with Speed Analytics Dashboard.', 'woospeed-analytics'); ?></i>
        </p>
    </div>

    <!-- Danger Zone -->
    <div
        style="margin-top: 30px; background: #fff; padding: 20px; border: 1px solid #dc3232; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-left-width: 4px;">
        <h3 style="color: #dc3232; margin-top:0;"><?php _e('⚠️ Danger Zone', 'woospeed-analytics'); ?></h3>
        <p><?php _e('Deletes ALL data generated by this plugin (Flat Table, Dummy Products, Dummy Orders).', 'woospeed-analytics'); ?>
        </p>
        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=woospeed-generator&seed_action=clear_all'), 'woospeed_seed_action'); ?>"
            class="button button-link-delete"
            style="color: #a00; text-decoration: none; border: 1px solid #dc3232; padding: 5px 10px; border-radius: 3px;"
            onclick="return confirm('<?php _e('ARE YOU SURE? This will delete all generated test data.', 'woospeed-analytics'); ?>');">
            <?php _e('🗑️ DELETE ALL DUMMY DATA', 'woospeed-analytics'); ?>
        </a>
    </div>
</div>