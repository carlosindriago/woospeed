<?php
/**
 * WooSpeed Repository Class
 * 
 * Handles all direct database interactions using plain SQL for performance.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooSpeed_Repository
{

    private $table_reports;
    private $table_items;

    public function __construct()
    {
        global $wpdb;
        $this->table_reports = $wpdb->prefix . 'wc_speed_reports';
        $this->table_items = $wpdb->prefix . 'wc_speed_order_items';
    }

    /**
     * Create custom tables on activation
     */
    public function create_tables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // 1. Reports Table
        $sql = "CREATE TABLE $this->table_reports (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            order_total decimal(10,2) NOT NULL,
            report_date date NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY order_id (order_id),
            KEY report_date (report_date) 
        ) $charset_collate;";

        // 2. Items Table
        $sql_items = "CREATE TABLE $this->table_items (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            product_name varchar(255) NOT NULL,
            quantity int(11) NOT NULL,
            line_total decimal(10,2) NOT NULL,
            report_date date NOT NULL,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY product_id (product_id),
            KEY report_date (report_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql_items);
    }

    /**
     * Save/Update order report
     */
    public function save_report($order_id, $total, $date)
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "INSERT INTO $this->table_reports (order_id, order_total, report_date) 
             VALUES (%d, %f, %s) 
             ON DUPLICATE KEY UPDATE order_total = VALUES(order_total), report_date = VALUES(report_date)",
            $order_id,
            $total,
            $date
        ));
    }

    /**
     * Save order items (clears previous items first)
     */
    public function save_items($order_id, $items, $date)
    {
        global $wpdb;
        $wpdb->delete($this->table_items, ['order_id' => $order_id]);

        foreach ($items as $item) {
            $wpdb->insert($this->table_items, [
                'order_id' => $order_id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'line_total' => $item['line_total'],
                'report_date' => $date
            ]);
        }
    }

    /**
     * Delete all data for an order
     */
    public function delete_order_data($order_id)
    {
        global $wpdb;
        $wpdb->delete($this->table_reports, ['order_id' => $order_id]);
        $wpdb->delete($this->table_items, ['order_id' => $order_id]);
    }

    /**
     * Get KPIs for a date range
     */
    public function get_kpis($start_date, $end_date)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COALESCE(SUM(order_total), 0) as revenue,
                COUNT(id) as orders,
                COALESCE(AVG(order_total), 0) as aov,
                COALESCE(MAX(order_total), 0) as max_order
             FROM $this->table_reports 
             WHERE report_date BETWEEN %s AND %s",
            $start_date,
            $end_date
        ));
    }

    /**
     * Get Chart Data
     */
    public function get_chart_data($start_date, $end_date)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT report_date, SUM(order_total) as total_sales 
             FROM $this->table_reports 
             WHERE report_date BETWEEN %s AND %s
             GROUP BY report_date 
             ORDER BY report_date ASC",
            $start_date,
            $end_date
        ));
    }

    /**
     * Get Leaderboard
     */
    public function get_top_products($start_date, $end_date, $limit = 5)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                product_name,
                product_id,
                SUM(quantity) as total_sold,
                SUM(line_total) as total_revenue
             FROM $this->table_items 
             WHERE report_date BETWEEN %s AND %s
             GROUP BY product_id, product_name
             ORDER BY total_sold DESC
             LIMIT %d",
            $start_date,
            $end_date,
            $limit
        ));
    }

    /**
     * Batch insert reports
     */
    public function batch_insert_reports($values)
    {
        global $wpdb;
        if (empty($values))
            return;
        $wpdb->query("INSERT IGNORE INTO $this->table_reports (order_id, order_total, report_date) VALUES " . implode(',', $values));
    }

    /**
     * Clean dummy data from custom tables
     */
    public function clean_dummy_tables($min_id = 9000000)
    {
        global $wpdb;
        $rows = $wpdb->query($wpdb->prepare("DELETE FROM $this->table_reports WHERE order_id >= %d", $min_id));
        $items = $wpdb->query($wpdb->prepare("DELETE FROM $this->table_items WHERE order_id >= %d", $min_id));
        return $rows + $items;
    }

    public function get_all_order_ids()
    {
        global $wpdb;
        return $wpdb->get_col("SELECT order_id FROM $this->table_reports");
    }

    public function has_items($order_id)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $this->table_items WHERE order_id = %d", $order_id)) > 0;
    }
}
