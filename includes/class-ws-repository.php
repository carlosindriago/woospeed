<?php
/**
 * WooSpeed Repository Class
 *
 * Handles all direct database interactions using plain SQL for performance.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooSpeed_Repository
{
    /**
     * @var string Custom reports table name
     */
    private string $table_reports;

    /**
     * @var string Custom order items table name
     */
    private string $table_items;

    /**
     * Constructor
     *
     * Initializes table names with WordPress database prefix.
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_reports = $wpdb->prefix . 'wc_speed_reports';
        $this->table_items = $wpdb->prefix . 'wc_speed_order_items';
    }

    /**
     * Create custom tables on activation
     *
     * Uses dbDelta for safe table creation/updates.
     *
     * @return bool True if tables created/updated successfully
     */
    public function create_tables(): bool
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

        return true;
    }

    /**
     * Save/Update order report
     *
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert logic.
     *
     * @param int $order_id WooCommerce order ID
     * @param float $total Order total amount
     * @param string $date Report date in Y-m-d format
     * @return bool True on success, false on failure
     */
    public function save_report(int $order_id, float $total, string $date): bool
    {
        global $wpdb;

        $result = $wpdb->query($wpdb->prepare(
            "INSERT INTO $this->table_reports (order_id, order_total, report_date)
             VALUES (%d, %f, %s)
             ON DUPLICATE KEY UPDATE order_total = VALUES(order_total), report_date = VALUES(report_date)",
            $order_id,
            $total,
            $date
        ));

        if ($result === false) {
            error_log(sprintf(
                '[WooSpeed] Failed to save report for order %d: %s',
                $order_id,
                $wpdb->last_error
            ));
            return false;
        }

        return true;
    }

    /**
     * Save order items (clears previous items first)
     *
     * @param int $order_id WooCommerce order ID
     * @param array $items Array of items with product_id, product_name, quantity, line_total
     * @param string $date Report date in Y-m-d format
     * @return bool True on success, false on failure
     */
    public function save_items(int $order_id, array $items, string $date): bool
    {
        global $wpdb;

        // Delete existing items for this order
        $wpdb->delete($this->table_items, ['order_id' => $order_id]);

        // Insert new items
        foreach ($items as $item) {
            $result = $wpdb->insert($this->table_items, [
                'order_id' => $order_id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'line_total' => $item['line_total'],
                'report_date' => $date
            ]);

            if ($result === false) {
                error_log(sprintf(
                    '[WooSpeed] Failed to save item for order %d: %s',
                    $order_id,
                    $wpdb->last_error
                ));
                return false;
            }
        }

        return true;
    }

    /**
     * Delete all data for an order
     *
     * @param int $order_id WooCommerce order ID
     * @return bool True on success, false on failure
     */
    public function delete_order_data(int $order_id): bool
    {
        global $wpdb;

        $reports_deleted = $wpdb->delete($this->table_reports, ['order_id' => $order_id]);
        $items_deleted = $wpdb->delete($this->table_items, ['order_id' => $order_id]);

        return ($reports_deleted !== false && $items_deleted !== false);
    }

    /**
     * Get KPIs for a date range
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return object|null Object with revenue, orders, aov, max_order properties
     */
    public function get_kpis(string $start_date, string $end_date): ?object
    {
        global $wpdb;

        $result = $wpdb->get_row($wpdb->prepare(
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

        return $result;
    }

    /**
     * Get Chart Data
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return array Array of objects with report_date and total_sales
     */
    public function get_chart_data(string $start_date, string $end_date): array
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT report_date, SUM(order_total) as total_sales
             FROM $this->table_reports
             WHERE report_date BETWEEN %s AND %s
             GROUP BY report_date
             ORDER BY report_date ASC",
            $start_date,
            $end_date
        ));

        return $results ?: [];
    }

    /**
     * Get Leaderboard - Top products
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int $limit Maximum number of products to return
     * @return array Array of product objects
     */
    public function get_top_products(string $start_date, string $end_date, int $limit = 5): array
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
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

        return $results ?: [];
    }

    /**
     * Batch insert reports
     *
     * WARNING: VALUES must be pre-prepared with $wpdb->prepare()
     *
     * @param array $values Array of prepared value strings
     * @return bool True on success
     */
    public function batch_insert_reports(array $values): bool
    {
        global $wpdb;

        if (empty($values)) {
            return false;
        }

        $result = $wpdb->query("INSERT IGNORE INTO $this->table_reports (order_id, order_total, report_date) VALUES " . implode(',', $values));

        return $result !== false;
    }

    /**
     * Clean dummy data from custom tables
     *
     * @param int $min_id Minimum order ID to delete (default: 9000000)
     * @return int Number of rows deleted
     */
    public function clean_dummy_tables(int $min_id = 9000000): int
    {
        global $wpdb;

        $rows = $wpdb->query($wpdb->prepare("DELETE FROM $this->table_reports WHERE order_id >= %d", $min_id));
        $items = $wpdb->query($wpdb->prepare("DELETE FROM $this->table_items WHERE order_id >= %d", $min_id));

        return (int)$rows + (int)$items;
    }

    /**
     * Get all order IDs from reports table
     *
     * @return array Array of order IDs
     */
    public function get_all_order_ids(): array
    {
        global $wpdb;

        $results = $wpdb->get_col("SELECT order_id FROM $this->table_reports");

        return $results ?: [];
    }

    /**
     * Check if an order has items recorded
     *
     * @param int $order_id WooCommerce order ID
     * @return bool True if order has items
     */
    public function has_items(int $order_id): bool
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $this->table_items WHERE order_id = %d", $order_id));

        return (int)$count > 0;
    }

    /**
     * Get sales grouped by day of week
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return array Array with weekday (0=Sunday, 6=Saturday), total_sales, order_count
     */
    public function get_weekday_sales(string $start_date, string $end_date): array
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT
                DAYOFWEEK(report_date) as weekday,
                SUM(order_total) as total_sales,
                COUNT(id) as order_count
             FROM $this->table_reports
             WHERE report_date BETWEEN %s AND %s
             GROUP BY DAYOFWEEK(report_date)
             ORDER BY weekday ASC",
            $start_date,
            $end_date
        ));

        return $results ?: [];
    }

    /**
     * Get the days with highest and lowest sales
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return object Object with best_day, best_total, worst_day, worst_total
     */
    public function get_extreme_days(string $start_date, string $end_date): object
    {
        global $wpdb;

        // Get best day
        $best = $wpdb->get_row($wpdb->prepare(
            "SELECT report_date as day, SUM(order_total) as total
             FROM $this->table_reports
             WHERE report_date BETWEEN %s AND %s
             GROUP BY report_date
             ORDER BY total DESC
             LIMIT 1",
            $start_date,
            $end_date
        ));

        // Get worst day
        $worst = $wpdb->get_row($wpdb->prepare(
            "SELECT report_date as day, SUM(order_total) as total
             FROM $this->table_reports
             WHERE report_date BETWEEN %s AND %s
             GROUP BY report_date
             ORDER BY total ASC
             LIMIT 1",
            $start_date,
            $end_date
        ));

        return (object) [
            'best_day' => $best ? $best->day : null,
            'best_total' => $best ? floatval($best->total) : 0,
            'worst_day' => $worst ? $worst->day : null,
            'worst_total' => $worst ? floatval($worst->total) : 0,
        ];
    }

    /**
     * Get least sold products (bottom performers)
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int $limit Maximum number of products to return
     * @return array Array of product objects
     */
    public function get_bottom_products(string $start_date, string $end_date, int $limit = 5): array
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT
                product_name,
                product_id,
                SUM(quantity) as total_sold,
                SUM(line_total) as total_revenue
             FROM $this->table_items
             WHERE report_date BETWEEN %s AND %s
             GROUP BY product_id, product_name
             ORDER BY total_sold ASC
             LIMIT %d",
            $start_date,
            $end_date,
            $limit
        ));

        return $results ?: [];
    }

    /**
     * Get top categories by revenue
     *
     * Joins with WooCommerce product terms.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int $limit Maximum number of categories to return
     * @return array Array of category objects
     */
    public function get_top_categories(string $start_date, string $end_date, int $limit = 5): array
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT
                t.name as category_name,
                t.term_id as category_id,
                SUM(oi.line_total) as total_revenue,
                SUM(oi.quantity) as total_sold
             FROM $this->table_items oi
             INNER JOIN {$wpdb->term_relationships} tr ON oi.product_id = tr.object_id
             INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'product_cat'
             INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
             WHERE oi.report_date BETWEEN %s AND %s
             GROUP BY t.term_id, t.name
             ORDER BY total_revenue DESC
             LIMIT %d",
            $start_date,
            $end_date,
            $limit
        );

        $results = $wpdb->get_results($query);

        return $results ?: [];
    }
}
