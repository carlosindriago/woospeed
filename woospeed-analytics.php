<?php
/*
Plugin Name: WooSpeed Analytics 🚀
Description: Demostración de Arquitectura de Alto Rendimiento. Reportes en 0.01s usando Tablas Planas y Raw SQL.
Version: 1.0.0
Author: Tu Nombre (The Senior Candidate)
*/

if (!defined('ABSPATH'))
    exit; // Seguridad: Nadie entra sin llave

class WooSpeed_Analytics
{

    private static $instance = null;
    private $table_name;

    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wc_speed_reports';

        // 1. Hooks de Instalación y Operación
        register_activation_hook(__FILE__, [$this, 'create_table']);
        add_action('woocommerce_order_status_completed', [$this, 'sync_order'], 10, 1);

        // 2. Hooks del Admin
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // 3. API Interna (AJAX)
        add_action('wp_ajax_woospeed_get_data', [$this, 'get_chart_data']);

        // 4. TRUCO DE SENSEI: Seeder de Datos Dummy (Solo para admin)
        // Se ejecuta si visitas: wp-admin/admin.php?page=woospeed-analytics&seed=1
        add_action('admin_init', [$this, 'seed_dummy_data']);
    }

    // 🏗️ ARQUITECTURA: Tabla Plana Optimizada
    public function create_table()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            order_total decimal(10,2) NOT NULL,
            report_date date NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY order_id (order_id),
            KEY report_date (report_date) 
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // 🔄 SYNC: El corazón del patrón CQRS
    // Copia datos de la "Write DB" (WooCommerce) a la "Read DB" (Nuestra tabla)
    public function sync_order($order_id)
    {
        global $wpdb;
        $order = wc_get_order($order_id);
        if (!$order)
            return;

        $total = $order->get_total();
        $date = $order->get_date_created()->date('Y-m-d');

        $wpdb->query($wpdb->prepare(
            "INSERT INTO $this->table_name (order_id, order_total, report_date) 
             VALUES (%d, %f, %s) 
             ON DUPLICATE KEY UPDATE order_total = VALUES(order_total), report_date = VALUES(report_date)",
            $order_id,
            $total,
            $date
        ));
    }

    // 🧪 SEEDER: Generador de Datos Falsos (Para presumir el gráfico)
    public function seed_dummy_data()
    {
        if (isset($_GET['page']) && $_GET['page'] == 'woospeed-analytics' && isset($_GET['seed']) && current_user_can('manage_options')) {
            global $wpdb;
            // Insertamos 5000 ventas simuladas en los últimos 60 días
            for ($i = 0; $i < 5000; $i++) {
                $days_ago = rand(0, 60);
                $date = date('Y-m-d', strtotime("-$days_ago days"));
                $total = rand(20, 300) + (rand(0, 99) / 100); // Precio aleatorio con decimales
                $order_id = 900000 + $i; // IDs falsos altos para no chocar

                $wpdb->query($wpdb->prepare(
                    "INSERT IGNORE INTO $this->table_name (order_id, order_total, report_date) 
                     VALUES (%d, %f, %s)",
                    $order_id,
                    $total,
                    $date
                ));
            }
            // Redireccionar para evitar re-envío y mostrar mensaje
            wp_redirect(admin_url('admin.php?page=woospeed-analytics&seeded=true'));
            exit;
        }
    }

    // 🚀 QUERY ENGINE: SQL Crudo y Rápido
    public function get_chart_data()
    {
        if (!current_user_can('manage_woocommerce'))
            wp_send_json_error('Unauthorized');

        global $wpdb;
        // La consulta optimizada usando índices
        $results = $wpdb->get_results(
            "SELECT report_date, SUM(order_total) as total_sales 
             FROM $this->table_name 
             WHERE report_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY report_date 
             ORDER BY report_date ASC"
        );
        wp_send_json_success($results);
    }

    // 🎨 FRONTEND: El Dashboard
    public function add_admin_menu()
    {
        add_submenu_page('woocommerce', 'Speed Analytics', 'Speed Analytics 🚀', 'manage_woocommerce', 'woospeed-analytics', [$this, 'render_admin_page']);
    }

    public function enqueue_assets($hook)
    {
        if ($hook != 'woocommerce_page_woospeed-analytics')
            return;
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    }

    public function render_admin_page()
    {
        global $wpdb;
        $start_time = microtime(true);
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name");
        $query_time = number_format(microtime(true) - $start_time, 5);
        ?>
                <div class="wrap">
                    <h1>🚀 WooCommerce High-Performance Analytics</h1>
                    <div style="background: #fff; border-left: 4px solid #007cba; padding: 12px 15px; margin: 15px 0; box-shadow: 0 1px 1px rgba(0,0,0,0.04);">
                        <p style="margin: 0; font-size: 14px; color: #3c434a;">
                            <b>Estado del Sistema:</b> Arquitectura de Tabla Plana Activa. <br>
                            <span style="display: inline-block; margin-top: 5px;">
                                📊 Ventas Procesadas: <b><?php echo number_format($total_orders); ?></b> | 
                                ⚡ Tiempo de Consulta SQL: <b><?php echo $query_time; ?>s</b>
                            </span>
                        </p>
                    </div>

                            <?php if (isset($_GET['seeded'])): ?>
                                    <div class="notice notice-success is-dismissible">
                                        <p>✅ ¡Datos dummy generados exitosamente!</p>
                                    </div>
                            <?php endif; ?>

                            <a href="<?php echo admin_url('admin.php?page=woospeed-analytics&seed=1'); ?>"
                                class="button button-secondary" onclick="return confirm('¿Generar 5000 ventas falsas?');">
                                🛠 Generar Datos de Prueba
                            </a>

                            <div
                                style="margin-top: 20px; background: white; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                                <canvas id="speedChart" height="100"></canvas>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const ctx = document.getElementById('speedChart').getContext('2d');

                                    fetch(ajaxurl + '?action=woospeed_get_data')
                                        .then(res => res.json())
                                        .then(response => {
                                            if (!response.success) return alert('Error API');

                                            const data = response.data;
                                            if (data.length === 0) {
                                                alert("No hay datos. ¡Usa el botón 'Generar Datos de Prueba'!");
                                                return;
                                            }

                                            new Chart(ctx, {
                                                type: 'line',
                                                data: {
                                                    labels: data.map(d => d.report_date),
                                                    datasets: [{
                                                        label: 'Ingresos Totales ($)',
                                                        data: data.map(d => d.total_sales),
                                                        borderColor: '#007cba',
                                                        backgroundColor: 'rgba(0, 124, 186, 0.1)',
                                                        borderWidth: 2,
                                                        fill: true,
                                                        tension: 0.3
                                                    }]
                                                },
                                                options: {
                                                    responsive: true,
                                                    plugins: { legend: { position: 'top' } },
                                                    scales: { y: { beginAtZero: true } }
                                                }
                                            });
                                        });
                                });
                            </script>
                </div>
                <?php
    }
}

// Iniciar el Singleton
WooSpeed_Analytics::get_instance();
