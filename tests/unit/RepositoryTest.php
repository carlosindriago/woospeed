<?php
/**
 * Repository Unit Tests
 *
 * Tests for WooSpeed_Repository class.
 *
 * @package WooSpeed_Analytics_Tests
 * @since 3.0.0
 */

use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    /**
     * @var WooSpeed_Repository
     */
    private WooSpeed_Repository $repository;

    /**
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * Set up test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        global $wpdb;
        $this->wpdb = $wpdb;
        $this->repository = new WooSpeed_Repository();

        // Clean tables before each test
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}wc_speed_reports");
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}wc_speed_order_items");
    }

    /**
     * Test create_tables method
     */
    public function test_create_tables(): void
    {
        $result = $this->repository->create_tables();

        $this->assertTrue($result);

        // Verify tables exist
        $reports_table = $this->wpdb->get_var("SHOW TABLES LIKE '{$this->wpdb->prefix}wc_speed_reports'");
        $items_table = $this->wpdb->get_var("SHOW TABLES LIKE '{$this->wpdb->prefix}wc_speed_order_items'");

        $this->assertNotNull($reports_table, 'Reports table should exist');
        $this->assertNotNull($items_table, 'Items table should exist');
    }

    /**
     * Test save_report method
     */
    public function test_save_report(): void
    {
        $order_id = 12345;
        $total = 99.99;
        $date = '2026-01-23';

        $result = $this->repository->save_report($order_id, $total, $date);

        $this->assertTrue($result, 'save_report should return true on success');

        // Verify data was saved
        $saved = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}wc_speed_reports WHERE order_id = %d",
            $order_id
        ));

        $this->assertNotNull($saved, 'Report should be saved in database');
        $this->assertEquals($total, $saved->order_total);
        $this->assertEquals($date, $saved->report_date);
    }

    /**
     * Test save_report with update (upsert)
     */
    public function test_save_report_updates_existing(): void
    {
        $order_id = 12346;
        $date = '2026-01-23';

        // First save
        $this->repository->save_report($order_id, 50.00, $date);

        // Update with different total
        $this->repository->save_report($order_id, 75.00, $date);

        // Verify it was updated, not duplicated
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}wc_speed_reports WHERE order_id = %d",
            $order_id
        ));

        $this->assertEquals(1, $count, 'Should have exactly one record');

        // Verify the total was updated
        $saved = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT order_total FROM {$this->wpdb->prefix}wc_speed_reports WHERE order_id = %d",
            $order_id
        ));

        $this->assertEquals(75.00, $saved->order_total, 'Total should be updated');
    }

    /**
     * Test save_items method
     */
    public function test_save_items(): void
    {
        $order_id = 12347;
        $date = '2026-01-23';
        $items = [
            [
                'product_id' => 101,
                'product_name' => 'Test Product 1',
                'quantity' => 2,
                'line_total' => 20.00
            ],
            [
                'product_id' => 102,
                'product_name' => 'Test Product 2',
                'quantity' => 1,
                'line_total' => 15.00
            ]
        ];

        $result = $this->repository->save_items($order_id, $items, $date);

        $this->assertTrue($result, 'save_items should return true on success');

        // Verify items were saved
        $saved_items = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}wc_speed_order_items WHERE order_id = %d",
            $order_id
        ));

        $this->assertCount(2, $saved_items, 'Should save exactly 2 items');
        $this->assertEquals('Test Product 1', $saved_items[0]->product_name);
    }

    /**
     * Test delete_order_data method
     */
    public function test_delete_order_data(): void
    {
        $order_id = 12348;
        $date = '2026-01-23';

        // Save report
        $this->repository->save_report($order_id, 100.00, $date);

        // Save items
        $this->repository->save_items($order_id, [
            [
                'product_id' => 101,
                'product_name' => 'Test Product',
                'quantity' => 1,
                'line_total' => 10.00
            ]
        ], $date);

        // Delete
        $result = $this->repository->delete_order_data($order_id);

        $this->assertTrue($result, 'delete_order_data should return true');

        // Verify deletion
        $reports = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}wc_speed_reports WHERE order_id = %d",
            $order_id
        ));

        $items = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}wc_speed_order_items WHERE order_id = %d",
            $order_id
        ));

        $this->assertEquals(0, $reports, 'Report should be deleted');
        $this->assertEquals(0, $items, 'Items should be deleted');
    }

    /**
     * Test get_kpis method
     */
    public function test_get_kpis(): void
    {
        $date = '2026-01-23';

        // Insert test data
        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 101,
            'order_total' => 100.00,
            'report_date' => $date
        ]);

        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 102,
            'order_total' => 200.00,
            'report_date' => $date
        ]);

        $kpis = $this->repository->get_kpis($date, $date);

        $this->assertNotNull($kpis, 'KPIs should not be null');
        $this->assertEquals(300.00, $kpis->revenue, 'Revenue should be 300');
        $this->assertEquals(2, $kpis->orders, 'Should have 2 orders');
        $this->assertEquals(150.00, $kpis->aov, 'AOV should be 150');
        $this->assertEquals(200.00, $kpis->max_order, 'Max order should be 200');
    }

    /**
     * Test get_kpis with empty database
     */
    public function test_get_kpis_empty_database(): void
    {
        $kpis = $this->repository->get_kpis('2026-01-01', '2026-01-31');

        $this->assertNotNull($kpis);
        $this->assertEquals(0, $kpis->revenue);
        $this->assertEquals(0, $kpis->orders);
        $this->assertEquals(0, $kpis->aov);
        $this->assertEquals(0, $kpis->max_order);
    }

    /**
     * Test get_chart_data method
     */
    public function test_get_chart_data(): void
    {
        $date = '2026-01-23';

        // Insert test data for multiple days
        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 101,
            'order_total' => 100.00,
            'report_date' => '2026-01-20'
        ]);

        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 102,
            'order_total' => 150.00,
            'report_date' => '2026-01-21'
        ]);

        $chart = $this->repository->get_chart_data('2026-01-20', '2026-01-21');

        $this->assertIsArray($chart);
        $this->assertCount(2, $chart, 'Should have 2 data points');
        $this->assertEquals('2026-01-20', $chart[0]->report_date);
        $this->assertEquals(100.00, $chart[0]->total_sales);
    }

    /**
     * Test get_top_products method
     */
    public function test_get_top_products(): void
    {
        $date = '2026-01-23';

        // Insert test items
        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_order_items", [
            'order_id' => 101,
            'product_id' => 201,
            'product_name' => 'Popular Product',
            'quantity' => 10,
            'line_total' => 100.00,
            'report_date' => $date
        ]);

        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_order_items", [
            'order_id' => 102,
            'product_id' => 202,
            'product_name' => 'Unpopular Product',
            'quantity' => 1,
            'line_total' => 10.00,
            'report_date' => $date
        ]);

        $top_products = $this->repository->get_top_products($date, $date, 5);

        $this->assertIsArray($top_products);
        $this->assertGreaterThanOrEqual(1, count($top_products));
        $this->assertEquals('Popular Product', $top_products[0]->product_name);
    }

    /**
     * Test has_items method
     */
    public function test_has_items(): void
    {
        $order_id = 103;
        $date = '2026-01-23';

        // Should return false initially
        $this->assertFalse($this->repository->has_items($order_id));

        // Add items
        $this->repository->save_items($order_id, [
            [
                'product_id' => 101,
                'product_name' => 'Test',
                'quantity' => 1,
                'line_total' => 10.00
            ]
        ], $date);

        // Should return true now
        $this->assertTrue($this->repository->has_items($order_id));
    }

    /**
     * Test clean_dummy_tables method
     */
    public function test_clean_dummy_tables(): void
    {
        // Insert dummy data with high IDs
        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 9000001,
            'order_total' => 100.00,
            'report_date' => '2026-01-23'
        ]);

        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_order_items", [
            'order_id' => 9000001,
            'product_id' => 101,
            'product_name' => 'Dummy',
            'quantity' => 1,
            'line_total' => 10.00,
            'report_date' => '2026-01-23'
        ]);

        // Clean with default threshold
        $deleted = $this->repository->clean_dummy_tables();

        $this->assertGreaterThan(0, $deleted);

        // Verify deletion
        $reports = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->wpdb->prefix}wc_speed_reports WHERE order_id >= 9000000");
        $items = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->wpdb->prefix}wc_speed_order_items WHERE order_id >= 9000000");

        $this->assertEquals(0, $reports);
        $this->assertEquals(0, $items);
    }

    /**
     * Test get_extreme_days method
     */
    public function test_get_extreme_days(): void
    {
        // Insert data for different days
        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 101,
            'order_total' => 500.00,
            'report_date' => '2026-01-20'
        ]);

        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 102,
            'order_total' => 50.00,
            'report_date' => '2026-01-21'
        ]);

        $extremes = $this->repository->get_extreme_days('2026-01-20', '2026-01-21');

        $this->assertNotNull($extremes);
        $this->assertEquals('2026-01-20', $extremes->best_day);
        $this->assertEquals(500.00, $extremes->best_total);
        $this->assertEquals('2026-01-21', $extremes->worst_day);
        $this->assertEquals(50.00, $extremes->worst_total);
    }

    /**
     * Test get_weekday_sales method
     */
    public function test_get_weekday_sales(): void
    {
        $date = '2026-01-23'; // This is a Thursday (weekday 5 in MySQL DAYOFWEEK)

        $this->wpdb->insert("{$this->wpdb->prefix}wc_speed_reports", [
            'order_id' => 101,
            'order_total' => 100.00,
            'report_date' => $date
        ]);

        $weekday_sales = $this->repository->get_weekday_sales($date, $date);

        $this->assertIsArray($weekday_sales);
        $this->assertGreaterThan(0, count($weekday_sales));
        $this->assertEquals(100.00, $weekday_sales[0]->total_sales);
    }

    /**
     * Test batch_insert_reports method
     */
    public function test_batch_insert_reports(): void
    {
        global $wpdb;

        // Prepare values (simulating prepared statements)
        $values = [
            $wpdb->prepare("(%d, %f, %s)", 9000101, 100.00, '2026-01-23'),
            $wpdb->prepare("(%d, %f, %s)", 9000102, 200.00, '2026-01-23'),
        ];

        $result = $this->repository->batch_insert_reports($values);

        $this->assertTrue($result);

        // Verify insertion
        $count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->wpdb->prefix}wc_speed_reports WHERE order_id >= 9000100");
        $this->assertEquals(2, $count);
    }

    /**
     * Test batch_insert_reports with empty array
     */
    public function test_batch_insert_reports_empty_array(): void
    {
        $result = $this->repository->batch_insert_reports([]);

        $this->assertFalse($result, 'Should return false for empty array');
    }
}
