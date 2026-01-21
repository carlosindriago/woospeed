<?php
/**
 * WooSpeed Seeder Class
 * 
 * Handles dummy data generation for stress testing.
 */

if (!defined('ABSPATH')) {
    exit;
}



class WooSpeed_Seeder
{

    private $repository;

    public function __construct()
    {
        $this->repository = new WooSpeed_Repository();
    }

    /**
     * Generate Dummy Products
     */
    public function seed_products($limit)
    {
        $count = 0;
        for ($i = 0; $i < $limit; $i++) {
            $product = new WC_Product_Simple();
            $product->set_name("Producto Demo Speed #" . rand(1000, 9999));
            $product->set_regular_price(rand(10, 100));
            $product->set_description("Descripción generada automáticamente para pruebas de carga.");
            $product->set_short_description("Producto de prueba.");
            $product->set_status("publish");
            $product->add_meta_data('_woospeed_dummy', 'yes', true);
            $product->save();
            $count++;
        }
        return $count;
    }

    /**
     * Generate Real WooCommerce Orders
     */
    public function seed_orders($limit)
    {
        $products = wc_get_products(['limit' => 10, 'status' => 'publish']);
        if (empty($products))
            return 0;

        add_filter('woocommerce_email_enabled', '__return_false');

        $count = 0;
        for ($i = 0; $i < $limit; $i++) {
            $order = wc_create_order();

            // Random Date (Last 90 days)
            $days_ago = rand(0, 90);
            $date = date('Y-m-d H:i:s', strtotime("-$days_ago days"));
            $order->set_date_created($date);
            $order->set_date_completed($date);
            $order->set_date_paid($date);

            // Add 1-3 random products
            for ($j = 0; $j < rand(1, 3); $j++) {
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
            $order->update_status('completed', 'Orden de prueba generada automáticamente.');
            $count++;
        }

        remove_filter('woocommerce_email_enabled', '__return_false');
        return $count;
    }

    /**
     * Seed direct analytics data (Bulk Insert)
     */
    public function seed_analytics_batch($limit)
    {
        global $wpdb; // Helper for prepare
        $values = [];
        $batch_size = 100;

        for ($i = 0; $i < $limit; $i++) {
            $days_ago = rand(0, 60);
            $date = date('Y-m-d', strtotime("-$days_ago days"));
            $total = rand(20, 300) + (rand(0, 99) / 100);
            $order_id = 9000000 + $i;

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
     */
    public function ensure_dummy_products()
    {
        $products = wc_get_products(['limit' => 1, 'tag' => ['_woospeed_dummy']]);
        $count = count(wc_get_products(['limit' => 10, 'status' => 'publish']));
        if ($count < 5) {
            $this->seed_products(20);
        }
    }
}
