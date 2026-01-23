<?php
/**
 * WooSpeed API Class
 *
 * Handles AJAX requests and API responses.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooSpeed_API
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

        // Register AJAX actions
        add_action('wp_ajax_woospeed_get_data', [$this, 'handle_get_data']);
        add_action('wp_ajax_woospeed_seed_batch', [$this, 'handle_batch_seed']);
        add_action('wp_ajax_woospeed_migrate_batch', [$this, 'handle_migrate_batch']);
    }

    /**
     * Handle Dashboard Data AJAX
     *
     * Returns KPIs, charts, and leaderboards for the specified date range.
     *
     * @return void
     */
    public function handle_get_data(): void
    {
        // Security check
        check_ajax_referer('woospeed_dashboard_nonce', 'security');

        // Authorization check
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // Parse dates - accept either start_date/end_date OR days for backward compatibility
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = sanitize_text_field($_GET['start_date']);
            $end_date = sanitize_text_field($_GET['end_date']);
        } else {
            $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
            $start_date = date('Y-m-d', strtotime("-$days days"));
            $end_date = date('Y-m-d');
        }

        // Validate date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            wp_send_json_error(['message' => 'Invalid date format']);
        }

        try {
            // Fetch Data from Repository
            $kpis = $this->repository->get_kpis($start_date, $end_date);
            $chart = $this->repository->get_chart_data($start_date, $end_date);
            $leaderboard = $this->repository->get_top_products($start_date, $end_date);

            // New v3.0 statistics
            $weekday_sales = $this->repository->get_weekday_sales($start_date, $end_date);
            $extreme_days = $this->repository->get_extreme_days($start_date, $end_date);
            $bottom_products = $this->repository->get_bottom_products($start_date, $end_date);
            $top_categories = $this->repository->get_top_categories($start_date, $end_date);

            // Format Response
            wp_send_json_success([
                'kpis' => [
                    'revenue' => floatval($kpis->revenue ?? 0),
                    'orders' => intval($kpis->orders ?? 0),
                    'aov' => round(floatval($kpis->aov ?? 0), 2),
                    'max_order' => floatval($kpis->max_order ?? 0)
                ],
                'chart' => $chart,
                'leaderboard' => $leaderboard,
                'weekday_sales' => $weekday_sales,
                'extreme_days' => $extreme_days,
                'bottom_products' => $bottom_products,
                'top_categories' => $top_categories,
                'period' => [
                    'start' => $start_date,
                    'end' => $end_date
                ]
            ]);
        } catch (Exception $e) {
            error_log(sprintf('[WooSpeed] API Error: %s', $e->getMessage()));
            wp_send_json_error(['message' => 'Failed to fetch dashboard data']);
        }
    }

    /**
     * Handle Batch Seeding AJAX
     *
     * Generates dummy orders for testing purposes.
     *
     * @return void
     */
    public function handle_batch_seed(): void
    {
        // Security check
        check_ajax_referer('woospeed_seed_nonce', 'security');

        // Authorization check
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 50;

        if ($batch_size < 1 || $batch_size > 1000) {
            wp_send_json_error(['message' => 'Batch size must be between 1 and 1000']);
        }

        set_time_limit(0);

        try {
            // Ensure products exist
            $this->seeder->ensure_dummy_products();

            // Generate Orders
            $count = $this->seeder->seed_orders($batch_size);

            $message = sprintf(__('Batch of %d orders completed.', 'woospeed-analytics'), $count);
            wp_send_json_success(['count' => $count, 'message' => $message]);
        } catch (Exception $e) {
            error_log(sprintf('[WooSpeed] Seeder Error: %s', $e->getMessage()));
            wp_send_json_error(['message' => 'Failed to generate orders']);
        }
    }

    /**
     * Handle Batch Order Migration AJAX
     *
     * Migrates existing WooCommerce orders to the flat table structure.
     *
     * @return void
     */
    public function handle_migrate_batch(): void
    {
        // Security check
        check_ajax_referer('woospeed_migration_nonce', 'security');

        // Authorization check
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 50;

        if ($batch_size < 1 || $batch_size > 500) {
            wp_send_json_error(['message' => 'Batch size must be between 1 and 500']);
        }

        // Get current migration status
        $migration = get_option('woospeed_migration_status', []);

        // Update status to in_progress if starting
        if ($offset === 0 || ($migration['status'] ?? '') === 'pending') {
            $migration['status'] = 'in_progress';
            $migration['started_at'] = current_time('mysql');
            update_option('woospeed_migration_status', $migration);
        }

        try {
            // Get WooCommerce orders to migrate
            $orders = wc_get_orders([
                'limit' => $batch_size,
                'offset' => $offset,
                'status' => ['completed', 'processing'],
                'orderby' => 'ID',
                'order' => 'ASC'
            ]);

            $migrated = 0;
            $errors = [];

            foreach ($orders as $order) {
                try {
                    $order_id = $order->get_id();
                    $total = $order->get_total();
                    $date = $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : date('Y-m-d');

                    // Save Report
                    if (!$this->repository->save_report($order_id, $total, $date)) {
                        throw new Exception('Failed to save report');
                    }

                    // Extract and Save Items
                    $items_data = $this->extract_order_items($order);

                    if (!$this->repository->save_items($order_id, $items_data, $date)) {
                        throw new Exception('Failed to save items');
                    }

                    $migrated++;
                } catch (Exception $e) {
                    $errors[] = sprintf(
                        __('Order #%d: %s', 'woospeed-analytics'),
                        $order->get_id(),
                        $e->getMessage()
                    );
                    error_log(sprintf('[WooSpeed] Migration Error for order %d: %s', $order->get_id(), $e->getMessage()));
                }
            }

            // Update migration status
            $migration = get_option('woospeed_migration_status', []);
            $migration['migrated_count'] = $offset + $migrated;
            $migration['error_count'] = ($migration['error_count'] ?? 0) + count($errors);

            // Check if migration is complete
            if (count($orders) < $batch_size) {
                $migration['status'] = 'completed';
                $migration['completed_at'] = current_time('mysql');
            }

            if (!empty($errors)) {
                $migration['errors'] = array_merge($migration['errors'] ?? [], $errors);
            }

            update_option('woospeed_migration_status', $migration);

            wp_send_json_success([
                'migrated_count' => $migration['migrated_count'],
                'error_count' => $migration['error_count'],
                'status' => $migration['status'],
                'errors' => $errors
            ]);
        } catch (Exception $e) {
            error_log(sprintf('[WooSpeed] Migration Batch Error: %s', $e->getMessage()));
            wp_send_json_error(['message' => 'Migration failed']);
        }
    }

    /**
     * Extract order items from a WooCommerce order
     *
     * Helper method to avoid code duplication.
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
}
