<?php
/*
Plugin Name: WooSpeed Analytics 🚀
Description: Herramienta para WooCommerce con arquitectura de alto rendimiento para generar reportes en 0.01s usando Tablas Planas y Raw SQL.
Version: 2.1.0
Author: Carlos Indriago
*/

if (!defined('ABSPATH')) {
    exit;
}

// 1. Constants
define('WS_VERSION', '2.1.0');
define('WS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WS_PLUGIN_URL', plugin_dir_url(__FILE__));

// 2. Autoload Components
require_once WS_PLUGIN_DIR . 'includes/class-ws-repository.php';
require_once WS_PLUGIN_DIR . 'includes/class-ws-seeder.php';
require_once WS_PLUGIN_DIR . 'includes/class-ws-api.php';
require_once WS_PLUGIN_DIR . 'admin/class-ws-admin.php';

// 3. Initialize Plugin
// 3. Initialize Plugin
function run_woospeed_analytics()
{
    // Load Text Domain
    load_plugin_textdomain('woospeed-analytics', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Initialize Admin UI
    $plugin = new WooSpeed_Admin();
    $plugin->run();

    // Initialize API (AJAX Endpoints)
    new WooSpeed_API();
}
run_woospeed_analytics();
