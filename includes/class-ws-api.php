<?php
/**
 * WooSpeed API Class
 * 
 * Handles AJAX requests and API responses.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooSpeed_API
{

    private $repository;
    private $seeder;

    public function __construct()
    {
        $this->repository = new WooSpeed_Repository();
        $this->seeder = new WooSpeed_Seeder();

        // Register AJAX actions
        add_action('wp_ajax_woospeed_get_data', [$this, 'handle_get_data']);
        add_action('wp_ajax_woospeed_seed_batch', [$this, 'handle_batch_seed']);
    }

    /**
     * Handle Dashboard Data AJAX
     */
    public function handle_get_data()
    {
        check_ajax_referer('woospeed_dashboard_nonce', 'security');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Unauthorized');
        }

        // Accept either start_date/end_date OR days for backward compatibility
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
            wp_send_json_error('Invalid date format');
        }

        // Fetch Data from Repository
        $kpis = $this->repository->get_kpis($start_date, $end_date);
        $chart = $this->repository->get_chart_data($start_date, $end_date);
        $leaderboard = $this->repository->get_top_products($start_date, $end_date);

        // Format Response
        wp_send_json_success([
            'kpis' => [
                'revenue' => floatval($kpis->revenue),
                'orders' => intval($kpis->orders),
                'aov' => round(floatval($kpis->aov), 2),
                'max_order' => floatval($kpis->max_order)
            ],
            'chart' => $chart,
            'leaderboard' => $leaderboard,
            'period' => [
                'start' => $start_date,
                'end' => $end_date
            ]
        ]);
    }

    /**
     * Handle Batch Seeding AJAX
     */
    public function handle_batch_seed()
    {
        check_ajax_referer('woospeed_seed_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 50;
        set_time_limit(0);

        // Ensure products exist
        $this->seeder->ensure_dummy_products();

        // Generate Orders
        $count = $this->seeder->seed_orders($batch_size);

        $message = sprintf(__('Batch of %d orders completed.', 'woospeed-analytics'), $count);
        wp_send_json_success(['count' => $count, 'message' => $message]);
    }
}
