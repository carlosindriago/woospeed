<?php
/*
Plugin Name: WooSpeed Analytics 🚀
Description: High-performance WooCommerce analytics engine with Flat Table architecture for 0.01s report generation.
Version: 3.0.0
Author: Carlos Indriago
Text Domain: woospeed-analytics
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================
// CONSTANTS
// ============================================================
define('WS_VERSION', '3.0.0');
define('WS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WS_PLUGIN_URL', plugin_dir_url(__FILE__));

// ============================================================
// AUTOLOAD COMPONENTS
// ============================================================
require_once WS_PLUGIN_DIR . 'includes/class-ws-repository.php';
require_once WS_PLUGIN_DIR . 'includes/class-ws-seeder.php';
require_once WS_PLUGIN_DIR . 'includes/class-ws-api.php';
require_once WS_PLUGIN_DIR . 'admin/class-ws-admin.php';

// ============================================================
// ACTIVATION HOOK
// ============================================================
register_activation_hook(__FILE__, 'woospeed_activate');

/**
 * Plugin activation handler
 *
 * Creates custom tables and checks for existing WooCommerce orders.
 *
 * @return void
 */
function woospeed_activate(): void
{
    // Create tables
    $repository = new WooSpeed_Repository();
    $repository->create_tables();

    // Check for existing WooCommerce orders
    $total_orders = woospeed_count_wc_orders();

    if ($total_orders > 0) {
        // Migration needed
        update_option('woospeed_migration_status', [
            'status' => 'pending',
            'total_orders' => $total_orders,
            'migrated_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'started_at' => null,
            'completed_at' => null
        ]);
    } else {
        // No migration needed
        update_option('woospeed_migration_status', [
            'status' => 'not_needed',
            'total_orders' => 0,
            'migrated_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'started_at' => null,
            'completed_at' => null
        ]);
    }
}

/**
 * Count WooCommerce orders that need migration
 *
 * @return int Number of orders
 */
function woospeed_count_wc_orders(): int
{
    if (!function_exists('wc_get_orders')) {
        return 0;
    }

    $orders = wc_get_orders([
        'limit' => -1,
        'status' => ['completed', 'processing'],
        'return' => 'ids'
    ]);

    return count($orders);
}

// ============================================================
// INITIALIZATION
// ============================================================
add_action('plugins_loaded', 'run_woospeed_analytics');

/**
 * Initialize the plugin
 *
 * @return void
 */
function run_woospeed_analytics(): void
{
    // Load Text Domain
    load_plugin_textdomain('woospeed-analytics', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Initialize Admin UI
    $plugin = new WooSpeed_Admin();
    $plugin->run();

    // Initialize API (AJAX Endpoints)
    new WooSpeed_API();
}
