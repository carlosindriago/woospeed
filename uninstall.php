<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WooSpeed Analytics
 */

// 🛡️ SEGURIDAD PRIMERO:
// Si este archivo es llamado directamente (no por WordPress), abortamos.
// Esto evita que alguien malintencionado borre tu tabla llamando al archivo desde el navegador.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Definimos el nombre de las tablas (igual que en el plugin principal)
$table_name = $wpdb->prefix . 'wc_speed_reports';
$items_table_name = $wpdb->prefix . 'wc_speed_order_items';

// 🗑️ LA LIMPIEZA:
// Borramos las tablas completamente.
// DROP TABLE IF EXISTS evita errores si las tablas ya no existieran.
$wpdb->query("DROP TABLE IF EXISTS $table_name");
$wpdb->query("DROP TABLE IF EXISTS $items_table_name");

// (Opcional) Si hubiéramos guardado configuraciones en wp_options, también las borraríamos aquí:
// delete_option('woospeed_settings');
