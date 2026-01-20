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

// Definimos el nombre de la tabla (igual que en el plugin principal)
$table_name = $wpdb->prefix . 'wc_speed_reports';

// 🗑️ LA LIMPIEZA:
// Borramos la tabla completamente.
// DROP TABLE IF EXISTS evita errores si la tabla ya no existiera.
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// (Opcional) Si hubiéramos guardado configuraciones en wp_options, también las borraríamos aquí:
// delete_option('woospeed_settings');
