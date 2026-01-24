<?php
/**
 * WooSpeed Admin Class
 *
 * Handles all admin-facing functionality (Menus, Enqueues, Views).
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooSpeed_Admin
{
    /**
     * @var WooSpeed_Repository Repository instance
     */
    private WooSpeed_Repository $repository;

    /**
     * @var WooSpeed_Seeder Seeder instance
     */
    private WooSpeed_Seeder $seeder;

    /**
     * Constructor
     *
     * @param WooSpeed_Repository|null $repository Optional repository instance
     * @param WooSpeed_Seeder|null $seeder Optional seeder instance
     */
    public function __construct(
        ?WooSpeed_Repository $repository = null,
        ?WooSpeed_Seeder $seeder = null
    ) {
        $this->repository = $repository ?? new WooSpeed_Repository();
        $this->seeder = $seeder ?? new WooSpeed_Seeder($this->repository);
    }

    /**
     * Initialize all admin hooks
     *
     * @return void
     */
    public function run(): void
    {
        // Admin Hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_init', [$this, 'maybe_upgrade_tables']);
        add_action('admin_init', [$this, 'handle_seed_actions']);
        add_action('admin_notices', [$this, 'show_migration_notice']);

        // WooCommerce Logic Hooks
        add_action('woocommerce_order_status_completed', [$this, 'sync_order'], 10, 1);
        add_action('woocommerce_order_status_changed', [$this, 'handle_status_change'], 10, 4);
    }

    /**
     * Show Migration Notice
     *
     * Displays admin notice when migration is needed or in progress.
     *
     * @return void
     */
    public function show_migration_notice(): void
    {
        $migration = get_option('woospeed_migration_status', []);
        $status = $migration['status'] ?? 'not_needed';

        if ($status === 'not_needed' || $status === 'completed') {
            return;
        }

        $total = $migration['total_orders'] ?? 0;
        $migrated = $migration['migrated_count'] ?? 0;
        $errors = $migration['error_count'] ?? 0;

        if ($status === 'pending') {
            $migrate_url = admin_url('admin.php?page=woospeed-migration');
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>🚀 WooSpeed Analytics:</strong>
                    <?php printf(
                        __('We detected %s existing orders that need to be synchronized for accurate analytics.', 'woospeed-analytics'),
                        '<strong>' . number_format($total) . '</strong>'
                    ); ?>
                </p>
                <p>
                    <a href="<?php echo esc_url($migrate_url); ?>" class="button button-primary">
                        <?php _e('Start Migration', 'woospeed-analytics'); ?>
                    </a>
                </p>
            </div>
            <?php
        } elseif ($status === 'in_progress') {
            $percent = $total > 0 ? round(($migrated / $total) * 100) : 0;
            ?>
            <div class="notice notice-info">
                <p>
                    <strong>⏳ WooSpeed Analytics:</strong>
                    <?php printf(
                        __('Migration in progress: %s of %s orders (%s%%)', 'woospeed-analytics'),
                        number_format($migrated),
                        number_format($total),
                        $percent
                    ); ?>
                </p>
            </div>
            <?php
        } elseif ($status === 'error') {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong>⚠️ WooSpeed Analytics:</strong>
                    <?php printf(
                        __('Migration encountered %s errors. %s of %s orders migrated.', 'woospeed-analytics'),
                        '<strong>' . $errors . '</strong>',
                        number_format($migrated),
                        number_format($total)
                    ); ?>
                </p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=woospeed-migration')); ?>" class="button">
                        <?php _e('View Details', 'woospeed-analytics'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Register Admin Pages
     *
     * @return void
     */
    public function add_admin_menu(): void
    {
        add_menu_page(
            __('WooSpeed Analytics', 'woospeed-analytics'),
            __('Speed Analytics', 'woospeed-analytics'),
            'manage_woocommerce',
            'woospeed-dashboard',
            [$this, 'render_dashboard_page'],
            'dashicons-chart-area',
            58
        );

        add_submenu_page(
            'woospeed-dashboard',
            __('Dashboard', 'woospeed-analytics'),
            __('Dashboard', 'woospeed-analytics'),
            'manage_woocommerce',
            'woospeed-dashboard',
            [$this, 'render_dashboard_page']
        );

        add_submenu_page(
            'woospeed-dashboard',
            __('Settings', 'woospeed-analytics'),
            __('Settings', 'woospeed-analytics'),
            'manage_woocommerce',
            'woospeed-settings',
            [$this, 'render_settings_page']
        );

        // Migration page (hidden from menu, accessible via notice or settings)
        add_submenu_page(
            null, // Hidden from menu
            __('Data Migration', 'woospeed-analytics'),
            __('Data Migration', 'woospeed-analytics'),
            'manage_woocommerce',
            'woospeed-migration',
            [$this, 'render_migration_page']
        );

        // Generator page (hidden, accessible via settings)
        add_submenu_page(
            null,
            __('Data Generator', 'woospeed-analytics'),
            __('Data Generator', 'woospeed-analytics'),
            'manage_woocommerce',
            'woospeed-generator',
            [$this, 'render_generator_page']
        );
    }

    /**
     * Render Dashboard Page
     *
     * @return void
     */
    public function render_dashboard_page(): void
    {
        include WS_PLUGIN_DIR . 'admin/partials/woospeed-dashboard-view.php';
    }

    /**
     * Render Settings Page
     *
     * @return void
     */
    public function render_settings_page(): void
    {
        include WS_PLUGIN_DIR . 'admin/partials/woospeed-settings-view.php';
    }

    /**
     * Render Migration Page
     *
     * @return void
     */
    public function render_migration_page(): void
    {
        include WS_PLUGIN_DIR . 'admin/partials/woospeed-migration-view.php';
    }

    /**
     * Render Generator Page
     *
     * @return void
     */
    public function render_generator_page(): void
    {
        include WS_PLUGIN_DIR . 'admin/partials/woospeed-generator-view.php';
    }

    /**
     * Enqueue Styles
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_styles(string $hook): void
    {
        if (strpos($hook, 'woospeed') === false) {
            return;
        }

        wp_enqueue_style('woospeed-admin', WS_PLUGIN_URL . 'assets/css/admin-style.css', [], WS_VERSION);
    }

    /**
     * Enqueue Scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_scripts(string $hook): void
    {
        if (strpos($hook, 'woospeed') === false) {
            return;
        }

        // Common Dependencies
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);

        // Dashboard Page
        if (strpos($hook, 'woospeed-dashboard') !== false) {
            wp_enqueue_script('woospeed-dashboard', WS_PLUGIN_URL . 'assets/js/admin-dashboard.js', ['jquery', 'chartjs'], WS_VERSION, true);

            wp_localize_script('woospeed-dashboard', 'woospeed_dashboard_vars', [
                'nonce' => wp_create_nonce('woospeed_dashboard_nonce'),
                'i18n' => [
                    'sold' => __('sold', 'woospeed-analytics'),
                    'load_time' => __('Load Time', 'woospeed-analytics'),
                    'no_data' => __('No data yet', 'woospeed-analytics'),
                    'revenue' => __('Revenue ($)', 'woospeed-analytics'),
                    'presets' => [
                        'today' => __('Today', 'woospeed-analytics'),
                        'yesterday' => __('Yesterday', 'woospeed-analytics'),
                        'week_to_date' => __('Week to date', 'woospeed-analytics'),
                        'last_week' => __('Last week', 'woospeed-analytics'),
                        'month_to_date' => __('Month to date', 'woospeed-analytics'),
                        'last_month' => __('Last month', 'woospeed-analytics'),
                        'quarter_to_date' => __('Quarter to date', 'woospeed-analytics'),
                        'last_quarter' => __('Last quarter', 'woospeed-analytics'),
                        'year_to_date' => __('Year to date', 'woospeed-analytics'),
                        'last_year' => __('Last year', 'woospeed-analytics'),
                        'custom' => __('Custom', 'woospeed-analytics')
                    ]
                ]
            ]);
        }

        // Generator Page
        if (strpos($hook, 'woospeed-generator') !== false) {
            wp_enqueue_script('woospeed-generator', WS_PLUGIN_URL . 'assets/js/admin-generator.js', ['jquery'], WS_VERSION, true);

            // Localize Nonce
            wp_localize_script('woospeed-generator', 'woospeed_vars', [
                'nonce' => wp_create_nonce('woospeed_seed_nonce'),
                'i18n' => [
                    'confirm_batch' => __('This will generate 5,000 real orders. Continue?', 'woospeed-analytics'),
                    'complete_batch' => __('✅ Process Complete! 5,000 Orders Generated.', 'woospeed-analytics'),
                    'error_network' => __('Network error. Try again.', 'woospeed-analytics'),
                    'error_process' => __('Error in process: ', 'woospeed-analytics')
                ]
            ]);
        }
    }

    /**
     * Sync Order on Completion
     *
     * Copies order data to the flat table structure when an order is completed.
     * Wrapped in try-catch to prevent order completion failures.
     *
     * @param int $order_id WooCommerce order ID
     * @return void
     */
    public function sync_order(int $order_id): void
    {
        try {
            $order = wc_get_order($order_id);

            if (!$order) {
                error_log(sprintf('[WooSpeed] Order %d not found during sync', $order_id));
                return;
            }

            $total = $order->get_total();
            $date = $order->get_date_created()->date('Y-m-d');

            // Save Report
            $report_saved = $this->repository->save_report($order_id, $total, $date);

            if (!$report_saved) {
                error_log(sprintf('[WooSpeed] Failed to save report for order %d', $order_id));
                return;
            }

            // Save Items
            $items_data = $this->extract_order_items($order);
            $items_saved = $this->repository->save_items($order_id, $items_data, $date);

            if (!$items_saved) {
                error_log(sprintf('[WooSpeed] Failed to save items for order %d', $order_id));
            }
        } catch (Exception $e) {
            error_log(sprintf('[WooSpeed] Error syncing order %d: %s', $order_id, $e->getMessage()));
            // Don't throw - allow order completion to proceed
        }
    }

    /**
     * Handle Order Status Change (Cleanup if cancelled)
     *
     * Wrapped in try-catch to prevent status change failures.
     *
     * @param int $order_id Order ID
     * @param string $from Status from
     * @param string $to Status to
     * @param WC_Order $order Order object
     * @return void
     */
    public function handle_status_change(int $order_id, string $from, string $to, WC_Order $order): void
    {
        try {
            $remove_statuses = ['cancelled', 'refunded', 'failed', 'trash'];
            $add_statuses = ['completed', 'processing'];

            if (in_array($to, $remove_statuses, true)) {
                $deleted = $this->repository->delete_order_data($order_id);

                if (!$deleted) {
                    error_log(sprintf('[WooSpeed] Failed to delete data for order %d', $order_id));
                }
            } elseif (in_array($to, $add_statuses, true)) {
                $this->sync_order($order_id);
            }
        } catch (Exception $e) {
            error_log(sprintf('[WooSpeed] Error handling status change for order %d: %s', $order_id, $e->getMessage()));
            // Don't throw - allow status change to proceed
        }
    }

    /**
     * Ensure tables exist
     *
     * @return void
     */
    public function maybe_upgrade_tables(): void
    {
        // Simple check: if table doesn't exist, create it.
        // Calling create_tables is safe due to dbDelta.
        $this->repository->create_tables();
    }

    /**
     * Handle Seed Actions (GET requests)
     *
     * @return void
     */
    public function handle_seed_actions(): void
    {
        if (!isset($_GET['page']) || !isset($_GET['seed_action']) || !current_user_can('manage_options')) {
            return;
        }

        // CSRF Protection
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'woospeed_seed_action')) {
            wp_die(__('Security check failed', 'woospeed-analytics'));
        }

        $action = $_GET['seed_action'];
        $count = 0;
        set_time_limit(300);

        try {
            if ($action === 'products_20') {
                $count = $this->seeder->seed_products(20);
            } elseif ($action === 'orders_50') {
                $count = $this->seeder->seed_orders(50);
            } elseif ($action === 'migrate_items') {
                $count = $this->migrate_existing_items();
                wp_redirect(admin_url("admin.php?page=woospeed-generator&migrated=true&count=$count"));
                exit;
            } elseif ($action === 'clear_all') {
                $count = $this->repository->clean_dummy_tables();
                $count += $this->clear_dummy_posts();
                wp_redirect(admin_url("admin.php?page=woospeed-generator&cleared=true&count=$count"));
                exit;
            }

            wp_redirect(admin_url("admin.php?page=woospeed-generator&seeded=true&type=$action&count=$count"));
            exit;
        } catch (Exception $e) {
            error_log(sprintf('[WooSpeed] Seed action error (%s): %s', $action, $e->getMessage()));
            wp_redirect(admin_url('admin.php?page=woospeed-generator&error=true'));
            exit;
        }
    }

    /**
     * Extract order items from a WooCommerce order
     *
     * Helper method to avoid code duplication (DRY principle).
     *
     * @param WC_Order $order The order object
     * @return array Array of item data
     */
    private function extract_order_items(WC_Order $order): array
    {
        $items_data = [];

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            if (!$product) {
                continue;
            }

            $items_data[] = [
                'product_id' => $product->get_id(),
                'product_name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'line_total' => $item->get_total(),
            ];
        }

        return $items_data;
    }

    /**
     * Clear dummy posts (products and orders)
     *
     * @return int Number of posts deleted
     */
    private function clear_dummy_posts(): int
    {
        $count = 0;

        // Products
        $products = wc_get_products([
            'limit' => -1,
            'meta_key' => '_woospeed_dummy',
            'meta_value' => 'yes',
            'return' => 'ids'
        ]);

        foreach ($products as $pid) {
            wp_delete_post($pid, true);
            $count++;
        }

        // Orders
        $orders = wc_get_orders([
            'limit' => -1,
            'meta_key' => '_woospeed_dummy',
            'meta_value' => 'yes',
            'return' => 'ids'
        ]);

        foreach ($orders as $oid) {
            wp_delete_post($oid, true);
            $count++;
        }

        return $count;
    }

    /**
     * Migrate existing items for orders that don't have them
     *
     * @return int Number of items migrated
     */
    private function migrate_existing_items(): int
    {
        $count = 0;
        $order_ids = $this->repository->get_all_order_ids();

        foreach ($order_ids as $order_id) {
            // Skip if already has items
            if ($this->repository->has_items($order_id)) {
                continue;
            }

            $order = wc_get_order($order_id);

            if (!$order) {
                continue;
            }

            $date = $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : date('Y-m-d');

            $items_data = $this->extract_order_items($order);
            $this->repository->save_items($order_id, $items_data, $date);

            $count += count($items_data);
        }

        return $count;
    }
}
