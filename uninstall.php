<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

// 🛡️ SEGURIDAD PRIMERO:
// Si este archivo es llamado directamente (no por WordPress), abortamos.
// Esto evita que alguien malintencionado borre tu tabla llamando al archivo desde el navegador.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// ============================================================
// DELETE CUSTOM TABLES
// ============================================================
$table_name = $wpdb->prefix . 'wc_speed_reports';
$items_table_name = $wpdb->prefix . 'wc_speed_order_items';

// DROP TABLE IF EXISTS evita errores si las tablas ya no existieran.
$wpdb->query("DROP TABLE IF EXISTS $table_name");
$wpdb->query("DROP TABLE IF EXISTS $items_table_name");

// ============================================================
// DELETE PLUGIN OPTIONS
// ============================================================
// Borramos TODAS las opciones que creamos
delete_option('woospeed_migration_status');

// Nota: Si en el futuro agregas más options con update_option(),
// agrégalas aquí para una limpieza completa.

// ============================================================
// LOG CLEANUP (opcional, para debugging)
// ============================================================
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('[WooSpeed] Plugin uninstalled - tables and options deleted');
}
