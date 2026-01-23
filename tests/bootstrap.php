<?php
/**
 * PHPUnit Bootstrap File
 *
 * Loads WordPress and the plugin before running tests.
 *
 * @package WooSpeed_Analytics_Tests
 */

$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Load WordPress test environment
require_once $_tests_dir . '/includes/functions.php';

// Manually load the plugin being tested
function _manually_load_plugin() {
    // Load WooCommerce first (dependency)
    $woocommerce_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/woocommerce/woocommerce.php';

    // If WooCommerce is not available, try to load from WP plugin dir
    if (!file_exists($woocommerce_path)) {
        $woocommerce_path = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
    }

    if (file_exists($woocommerce_path)) {
        require_once $woocommerce_path;
    }

    // Load our plugin
    require dirname(dirname(__FILE__)) . '/woospeed-analytics.php';
}

tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Activate the plugin (needed for tables creation)
activate_plugin('woospeed-analytics/woospeed-analytics.php');

// Use existing installation (don't reinstall WordPress)
// This makes tests run much faster
define('WP_USE_THEMES', false);

// Set up error reporting for better debugging
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', true);
}

if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}

// Increase memory limit for tests
@ini_set('memory_limit', '512M');

// Set default timezone
date_default_timezone_set('UTC');

// Disable email sending during tests
tests_add_filter('wp_mail', '__return_false');

// Disable HTTP requests
tests_add_filter('pre_http_request', function() {
    return new WP_Error('http_request_disabled', 'HTTP requests disabled in tests');
});

// Clean up any existing test data
function _clean_test_data() {
    global $wpdb;

    // Clean our custom tables
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wc_speed_reports");
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wc_speed_order_items");

    // Clean options
    delete_option('woospeed_migration_status');
}

// Clean before each test
tests_add_filter('wp setUp', '_clean_test_data');

// Echo success message (only visible when running tests directly)
if (defined('PHPUNIT_RUNNER') || php_sapi_name() === 'cli') {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "WooSpeed Analytics Test Suite Loaded\n";
    echo "WordPress: " . get_bloginfo('version') . "\n";
    echo "PHP: " . PHP_VERSION . "\n";
    echo str_repeat('=', 70) . "\n\n";
}
