<?php
/**
 * WooSpeed Seeder Class
 *
 * Handles dummy data generation for stress testing.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooSpeed_Seeder
{
    /**
     * @var WooSpeed_Repository Repository instance
     */
    private WooSpeed_Repository $repository;

    /**
     * Order ID offset for dummy orders to avoid conflicts
     */
    private const DUMMY_ORDER_ID_OFFSET = 9000000;

    /**
     * Constructor
     *
     * @param WooSpeed_Repository|null $repository Optional repository instance
     */
    public function __construct(?WooSpeed_Repository $repository = null)
    {
        $this->repository = $repository ?? new WooSpeed_Repository();
    }

    /**
     * Generate Dummy Products
     *
     * Creates simple products with meta tag for identification.
     *
     * @param int $limit Number of products to create
     * @return int Number of products created
     */
    public function seed_products(int $limit): int
    {
        $count = 0;

        for ($i = 0; $i < $limit; $i++) {
            $product = new WC_Product_Simple();
            $product->set_name(sprintf(__("Speed Demo Product #%d", 'woospeed-analytics'), rand(1000, 9999)));
            $product->set_regular_price(rand(10, 100));
            $product->set_description(__("Automatically generated description for load testing.", 'woospeed-analytics'));
            $product->set_short_description(__("Test product.", 'woospeed-analytics'));
            $product->set_status("publish");
            $product->add_meta_data('_woospeed_dummy', 'yes', true);

            $result = $product->save();

            if ($result) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate Real WooCommerce Orders
     *
     * Creates complete orders with random dates within the last 90 days.
     *
     * @param int $limit Number of orders to create
     * @return int Number of orders created
     */
    public function seed_orders(int $limit): int
    {
        $products = wc_get_products(['limit' => 10, 'status' => 'publish']);

        if (empty($products)) {
            return 0;
        }

        // Disable emails during seeding
        add_filter('woocommerce_email_enabled', '__return_false');

        $count = 0;

        for ($i = 0; $i < $limit; $i++) {
            try {
                $order = wc_create_order();

                // Random Date (Last 90 days)
                $days_ago = rand(0, 90);
                $date = date('Y-m-d H:i:s', strtotime("-$days_ago days"));

                $order->set_date_created($date);
                $order->set_date_completed($date);
                $order->set_date_paid($date);

                // Add 1-3 random products
                $num_products = rand(1, 3);
                for ($j = 0; $j < $num_products; $j++) {
                    $random_product = $products[array_rand($products)];
                    $order->add_product($random_product, rand(1, 3));
                }

                // Dummy Address
                $address = [
                    'first_name' => 'Test',
                    'last_name' => 'User ' . $i,
                    'email' => "testuser$i@example.com",
                    'phone' => '555-0123',
                    'address_1' => '123 Fake St',
                    'city' => 'Tech City',
                    'state' => 'CA',
                    'postcode' => '90210',
                    'country' => 'US'
                ];

                $order->set_address($address, 'billing');
                $order->calculate_totals();
                $order->add_meta_data('_woospeed_dummy', 'yes', true);

                // This triggers the 'completed' hook which syncs to our DB via the main plugin class
                $order->update_status('completed', __('Automatically generated test order.', 'woospeed-analytics'));

                $count++;
            } catch (Exception $e) {
                error_log(sprintf('[WooSpeed] Failed to seed order %d: %s', $i, $e->getMessage()));
            }
        }

        remove_filter('woocommerce_email_enabled', '__return_false');

        return $count;
    }

    /**
     * Seed direct analytics data (Bulk Insert)
     *
     * Bypasses WooCommerce order creation for faster data generation.
     *
     * @param int $limit Number of records to create
     * @return int Number of records created
     */
    public function seed_analytics_batch(int $limit): int
    {
        global $wpdb;

        $values = [];
        $batch_size = 100;

        for ($i = 0; $i < $limit; $i++) {
            $days_ago = rand(0, 60);
            $date = date('Y-m-d', strtotime("-$days_ago days"));
            $total = rand(20, 300) + (rand(0, 99) / 100);
            $order_id = self::DUMMY_ORDER_ID_OFFSET + $i;

            $values[] = $wpdb->prepare("(%d, %f, %s)", $order_id, $total, $date);

            if (count($values) >= $batch_size || $i === $limit - 1) {
                $this->repository->batch_insert_reports($values);
                $values = [];
            }
        }

        return $limit;
    }

    /**
     * Ensure we have products to create orders from
     *
     * Checks if dummy products exist, creates them if needed.
     *
     * @return void
     */
    public function ensure_dummy_products(): void
    {
        $products = wc_get_products(['limit' => 1, 'tag' => ['_woospeed_dummy']]);
        $count = count(wc_get_products(['limit' => 10, 'status' => 'publish']));

        if ($count < 5) {
            $this->seed_products(20);
        }
    }
}
