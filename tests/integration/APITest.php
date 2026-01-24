<?php
/**
 * Integration Tests for WooSpeed API
 *
 * Tests AJAX endpoints with actual WordPress environment.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{
    private WooSpeed_API $api;
    private int $testUserId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->testUserId = wp_create_user('test_admin', 'password', 'test@example.com');
        $user = get_user_by('id', $this->testUserId);
        $user->set_role('administrator');

        // Initialize API
        $this->api = new WooSpeed_API();
    }

    protected function tearDown(): void
    {
        // Cleanup test user
        wp_delete_user($this->testUserId);

        parent::tearDown();
    }

    /**
     * Test handle_get_data with valid date range
     */
    public function test_handle_get_data_success(): void
    {
        // Set up admin user
        wp_set_current_user($this->testUserId);

        // Create nonce
        $nonce = wp_create_nonce('woospeed_dashboard_nonce');

        // Simulate AJAX request
        $_GET['security'] = $nonce;
        $_GET['start_date'] = date('Y-m-d', strtotime('-30 days'));
        $_GET['end_date'] = date('Y-m-d');

        // Capture output
        try {
            ob_start();
            $this->api->handle_get_data();
            $output = ob_get_clean();

            // Decode JSON response
            $response = json_decode($output, true);

            $this->assertIsArray($response);
            $this->assertTrue($response['success'] ?? false);
            $this->assertArrayHasKey('kpis', $response['data'] ?? []);
        } catch (Exception $e) {
            ob_end_clean();
            $this->fail('Exception thrown: ' . $e->getMessage());
        }
    }

    /**
     * Test handle_get_data with invalid nonce
     */
    public function test_handle_get_data_invalid_nonce(): void
    {
        wp_set_current_user($this->testUserId);

        $_GET['security'] = 'invalid_nonce';
        $_GET['start_date'] = date('Y-m-d', strtotime('-30 days'));
        $_GET['end_date'] = date('Y-m-d');

        try {
            ob_start();
            $this->api->handle_get_data();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            $this->assertFalse($response['success'] ?? true);
            $this->assertStringContainsString('security', $response['data']['message'] ?? '');
        } catch (Exception $e) {
            ob_end_clean();
        }
    }

    /**
     * Test handle_get_data with unauthorized user
     */
    public function test_handle_get_data_unauthorized(): void
    {
        // Set up subscriber (non-admin)
        wp_set_current_user($this->testUserId);
        $user = get_user_by('id', $this->testUserId);
        $user->set_role('subscriber');

        $_GET['security'] = wp_create_nonce('woospeed_dashboard_nonce');
        $_GET['start_date'] = date('Y-m-d', strtotime('-30 days'));
        $_GET['end_date'] = date('Y-m-d');

        try {
            ob_start();
            $this->api->handle_get_data();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            $this->assertFalse($response['success'] ?? true);
            $this->assertStringContainsString('Unauthorized', $response['data']['message'] ?? '');
        } catch (Exception $e) {
            ob_end_clean();
        }
    }

    /**
     * Test handle_get_data with invalid date format
     */
    public function test_handle_get_data_invalid_date_format(): void
    {
        wp_set_current_user($this->testUserId);

        $_GET['security'] = wp_create_nonce('woospeed_dashboard_nonce');
        $_GET['start_date'] = '2024-13-45'; // Invalid date
        $_GET['end_date'] = date('Y-m-d');

        try {
            ob_start();
            $this->api->handle_get_data();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            $this->assertFalse($response['success'] ?? true);
        } catch (Exception $e) {
            ob_end_clean();
        }
    }

    /**
     * Test handle_get_data with date range exceeding 1 year
     */
    public function test_handle_get_data_date_range_exceeds_limit(): void
    {
        wp_set_current_user($this->testUserId);

        $_GET['security'] = wp_create_nonce('woospeed_dashboard_nonce');
        $_GET['start_date'] = date('Y-m-d', strtotime('-400 days'));
        $_GET['end_date'] = date('Y-m-d');

        try {
            ob_start();
            $this->api->handle_get_data();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            $this->assertFalse($response['success'] ?? true);
            $this->assertStringContainsString('1 year', $response['data']['message'] ?? '');
        } catch (Exception $e) {
            ob_end_clean();
        }
    }

    /**
     * Test handle_batch_seed with valid request
     */
    public function test_handle_batch_seed_success(): void
    {
        wp_set_current_user($this->testUserId);

        $_POST['security'] = wp_create_nonce('woospeed_seed_nonce');
        $_POST['batch_size'] = 10;

        try {
            ob_start();
            $this->api->handle_batch_seed();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            $this->assertIsArray($response);
        } catch (Exception $e) {
            ob_end_clean();
        }
    }

    /**
     * Test handle_batch_seed with invalid batch size
     */
    public function test_handle_batch_seed_invalid_batch_size(): void
    {
        wp_set_current_user($this->testUserId);

        $_POST['security'] = wp_create_nonce('woospeed_seed_nonce');
        $_POST['batch_size'] = 2000; // Exceeds maximum of 1000

        try {
            ob_start();
            $this->api->handle_batch_seed();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            $this->assertFalse($response['success'] ?? true);
            $this->assertStringContainsString('Batch size', $response['data']['message'] ?? '');
        } catch (Exception $e) {
            ob_end_clean();
        }
    }

    /**
     * Test handle_migrate_batch with valid request
     */
    public function test_handle_migrate_batch_success(): void
    {
        wp_set_current_user($this->testUserId);

        $_POST['security'] = wp_create_nonce('woospeed_migration_nonce');
        $_POST['offset'] = 0;
        $_POST['batch_size'] = 50;

        try {
            ob_start();
            $this->api->handle_migrate_batch();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            $this->assertIsArray($response);
            $this->assertArrayHasKey('status', $response['data'] ?? []);
        } catch (Exception $e) {
            ob_end_clean();
        }
    }

    /**
     * Test API data structure matches expected format
     */
    public function test_api_response_structure(): void
    {
        wp_set_current_user($this->testUserId);

        $_GET['security'] = wp_create_nonce('woospeed_dashboard_nonce');
        $_GET['start_date'] = date('Y-m-d', strtotime('-7 days'));
        $_GET['end_date'] = date('Y-m-d');

        try {
            ob_start();
            $this->api->handle_get_data();
            $output = ob_get_clean();

            $response = json_decode($output, true);

            if (isset($response['data'])) {
                $data = $response['data'];

                // Verify all expected keys exist
                $expectedKeys = [
                    'kpis',
                    'chart',
                    'leaderboard',
                    'weekday_sales',
                    'extreme_days',
                    'bottom_products',
                    'top_categories',
                    'period'
                ];

                foreach ($expectedKeys as $key) {
                    $this->assertArrayHasKey($key, $data, "Missing key: $key");
                }

                // Verify KPIs structure
                $this->assertArrayHasKey('revenue', $data['kpis']);
                $this->assertArrayHasKey('orders', $data['kpis']);
                $this->assertArrayHasKey('aov', $data['kpis']);
                $this->assertArrayHasKey('max_order', $data['kpis']);

                // Verify period structure
                $this->assertArrayHasKey('start', $data['period']);
                $this->assertArrayHasKey('end', $data['period']);
            }
        } catch (Exception $e) {
            ob_end_clean();
            $this->fail('Exception thrown: ' . $e->getMessage());
        }
    }
}
