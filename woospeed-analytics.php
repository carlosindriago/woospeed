<?php
/*
Plugin Name: WooSpeed Analytics 🚀
Description: Herramienta para WooCommerce con arquitectura de alto rendimiento para generar reportes en 0.01s usando Tablas Planas y Raw SQL.
Version: 1.2.0
Author: Carlos Indriago
*/

if (!defined('ABSPATH'))
    exit; // Seguridad: Nadie entra sin llave

class WooSpeed_Analytics
{

    private static $instance = null;
    private $table_name;
    private $items_table_name; // Nueva tabla de items granulares

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
        $this->items_table_name = $wpdb->prefix . 'wc_speed_order_items';

        // 1. Hooks de Instalación y Operación
        register_activation_hook(__FILE__, [$this, 'create_table']);
        add_action('woocommerce_order_status_completed', [$this, 'sync_order'], 10, 1);
        add_action('woocommerce_order_status_changed', [$this, 'handle_status_change'], 10, 4);

        // 2. Hooks del Admin
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // 3. API Interna (AJAX)
        add_action('wp_ajax_woospeed_get_data', [$this, 'get_chart_data']);
        add_action('wp_ajax_woospeed_seed_batch', [$this, 'handle_batch_seed']);

        // 4. Seeder Handlers
        add_action('admin_init', [$this, 'handle_seed_actions']);
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

        // 🎯 TABLA GRANULAR: Items de cada orden (para Top Products)
        $sql_items = "CREATE TABLE $this->items_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            product_name varchar(255) NOT NULL,
            quantity int(11) NOT NULL,
            line_total decimal(10,2) NOT NULL,
            report_date date NOT NULL,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY product_id (product_id),
            KEY report_date (report_date)
        ) $charset_collate;";
        dbDelta($sql_items);
    }

    // 🔄 SYNC: El corazón del patrón CQRS
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

        // 🎯 SYNC ITEMS: Guardar detalle de productos para Top Products
        // Primero borramos items anteriores (por si es update)
        $wpdb->delete($this->items_table_name, ['order_id' => $order_id]);

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product)
                continue;

            $wpdb->insert($this->items_table_name, [
                'order_id' => $order_id,
                'product_id' => $product->get_id(),
                'product_name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'line_total' => $item->get_total(),
                'report_date' => $date
            ]);
        }
    }

    // 🔄 LIFECYCLE: Manejo de cancelaciones y devoluciones
    public function handle_status_change($order_id, $from, $to, $order)
    {
        global $wpdb;

        // Si el nuevo estado NO es pagado (ej: cancelled, refunded, failed)
        // Borramos la entrada de nuestra tabla de reportes para mantener la verdad.
        if (in_array($to, ['cancelled', 'refunded', 'failed', 'trash'])) {
            $wpdb->delete($this->table_name, ['order_id' => $order_id]);
            $wpdb->delete($this->items_table_name, ['order_id' => $order_id]); // Limpiar items también
        }
        // Si vuelve a ser 'completed' o 'processing', la sincronizamos
        elseif (in_array($to, ['completed', 'processing'])) {
            $this->sync_order($order_id);
        }
    }

    // 🎨 FRONTEND: Nueva Estructura de Menú
    public function add_admin_menu()
    {
        add_menu_page(
            'WooSpeed Analytics',
            'Speed Analytics',
            'manage_woocommerce',
            'woospeed-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-chart-area',
            58
        );

        add_submenu_page(
            'woospeed-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_woocommerce',
            'woospeed-dashboard',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'woospeed-dashboard',
            'Generador de Datos',
            'Generador de Datos',
            'manage_woocommerce',
            'woospeed-generator',
            [$this, 'render_generator_page']
        );
    }

    // ⚙️ SEEDER HANDLER
    public function handle_seed_actions()
    {
        if (!isset($_GET['page']) || !isset($_GET['seed_action']) || !current_user_can('manage_options'))
            return;

        $action = $_GET['seed_action'];
        $count = 0;

        // Aumentar tiempo de ejecución para generaciones grandes
        set_time_limit(300);

        if ($action === 'analytics_5k') {
            $count = $this->seed_analytics_data(5000);
        } elseif ($action === 'products_20') {
            $count = $this->seed_wc_products(20);
        } elseif ($action === 'orders_50') {
            $count = $this->seed_wc_orders(50);
        } elseif ($action === 'clear_all') {
            $count = $this->clear_dummy_data();
            wp_redirect(admin_url("admin.php?page=woospeed-generator&cleared=true&count=$count"));
            exit;
        }

        wp_redirect(admin_url("admin.php?page=woospeed-generator&seeded=true&type=$action&count=$count"));
        exit;
    }

    // 🧹 CLEANER: Borra todo lo generado
    private function clear_dummy_data()
    {
        global $wpdb;
        $count = 0;

        // 1. Borrar Tabla Plana (Solo IDs altos dummy)
        $deleted_rows = $wpdb->query("DELETE FROM $this->table_name WHERE order_id >= 9000000");
        $count += $deleted_rows;

        // 1.1 Borrar Items de órdenes dummy
        $deleted_items = $wpdb->query("DELETE FROM $this->items_table_name WHERE order_id >= 9000000");
        $count += $deleted_items;

        // 2. Borrar Productos Dummy (Meta Tag + Legacy Pattern)
        $dummy_products = wc_get_products([
            'limit' => -1,
            'meta_key' => '_woospeed_dummy',
            'meta_value' => 'yes',
            'return' => 'ids'
        ]);

        // Backup: Buscar por nombre si no tienen meta (Legacy)
        if (empty($dummy_products)) {
            $legacy_products = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_title LIKE 'Producto Demo Speed #%'");
            $dummy_products = array_merge($dummy_products, $legacy_products);
        }

        foreach ($dummy_products as $pid) {
            wp_delete_post($pid, true);
            $count++;
        }

        // 3. Borrar Órdenes Dummy (Meta Tag + Legacy Email Pattern)
        $dummy_orders = wc_get_orders([
            'limit' => -1,
            'meta_key' => '_woospeed_dummy',
            'meta_value' => 'yes',
            'return' => 'ids'
        ]);

        // Backup: Buscar por email (Legacy)
        if (empty($dummy_orders)) {
            // Buscamos ordenes donde el billing_email empiece con testuser
            $legacy_orders = $wpdb->get_col("
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_billing_email' 
                AND meta_value LIKE 'testuser%@example.com'
            ");
            $dummy_orders = array_merge($dummy_orders, $legacy_orders);
        }

        foreach ($dummy_orders as $oid) {
            wp_delete_post($oid, true);
            $count++;
        }

        return $count;
    }

    // 1. Generador Analytics (SQL Directo) - OPTIMIZADO con Bulk Insert
    private function seed_analytics_data($limit)
    {
        global $wpdb;
        $values = [];
        $batch_size = 100; // Insertar de 100 en 100

        for ($i = 0; $i < $limit; $i++) {
            $days_ago = rand(0, 60);
            $date = date('Y-m-d', strtotime("-$days_ago days"));
            $total = rand(20, 300) + (rand(0, 99) / 100);
            $order_id = 9000000 + $i;

            $values[] = $wpdb->prepare("(%d, %f, %s)", $order_id, $total, $date);

            // Ejecutar batch cada 100 registros o al final
            if (count($values) >= $batch_size || $i === $limit - 1) {
                $wpdb->query("INSERT IGNORE INTO $this->table_name (order_id, order_total, report_date) VALUES " . implode(',', $values));
                $values = []; // Reset para el siguiente batch
            }
        }
        return $limit;
    }

    // 2. Generador Productos Reales
    private function seed_wc_products($limit)
    {
        $count = 0;
        for ($i = 0; $i < $limit; $i++) {
            $product = new WC_Product_Simple();
            $product->set_name("Producto Demo Speed #" . rand(1000, 9999));
            $product->set_regular_price(rand(10, 100));
            $product->set_description("Descripción generada automáticamente para pruebas de carga.");
            $product->set_short_description("Producto de prueba.");
            $product->set_status("publish");
            $product->add_meta_data('_woospeed_dummy', 'yes', true); // Tag para borrado fácil
            $product->save();
            $count++;
        }
        return $count;
    }

    // 3. Generador Órdenes Reales (Dispara Hooks) - OPTIMIZADO
    private function seed_wc_orders($limit)
    {
        $products = wc_get_products(['limit' => 10, 'status' => 'publish']);
        if (empty($products))
            return 0;

        // 🔇 Suprimir emails durante seeding
        add_filter('woocommerce_email_enabled', '__return_false');

        $count = 0;
        for ($i = 0; $i < $limit; $i++) {
            $order = wc_create_order();

            // 📅 Fecha Aleatoria (Últimos 90 días) para simular historial
            $days_ago = rand(0, 90);
            $date = date('Y-m-d H:i:s', strtotime("-$days_ago days"));
            $order->set_date_created($date);
            $order->set_date_completed($date);
            $order->set_date_paid($date);

            // Agregar 1-3 productos al azar
            for ($j = 0; $j < rand(1, 3); $j++) {
                $random_product = $products[array_rand($products)];
                $order->add_product($random_product, rand(1, 3));
            }
            // Dirección Dummy
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

            // 🏷️ Tag para limpieza fácil
            $order->add_meta_data('_woospeed_dummy', 'yes', true);

            // Marcar como completada dispara el hook 'woospeed-analytics' automáticamente
            $order->update_status('completed', 'Orden de prueba generada automáticamente.');
            $count++;
        }

        // 🔊 Restaurar emails
        remove_filter('woocommerce_email_enabled', '__return_false');

        return $count;
    }

    // 🚀 AJAX BATCH HANDLER
    public function handle_batch_seed()
    {
        // Verificar Nonce de Seguridad (CSRF) 🛡️
        check_ajax_referer('woospeed_seed_nonce', 'security'); // 🛑 El Portero verifica la llave

        // Verificar permisos y nonce si fuera necesario (simplificado para PoC)
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 50;
        set_time_limit(0); // Evitar timeout en este batch

        // 1. Asegurar Productos
        $this->ensure_dummy_products();

        // 2. Generar Batch de Órdenes
        $count = $this->seed_wc_orders($batch_size);

        wp_send_json_success(['count' => $count, 'message' => "Batch de $count órdenes completado."]);
    }

    private function ensure_dummy_products()
    {
        $products = wc_get_products(['limit' => 1, 'tag' => ['_woospeed_dummy']]);
        // Si hay menos de 10 productos, generamos 20 más para asegurar variedad
        $count = count(wc_get_products(['limit' => 10, 'status' => 'publish']));
        if ($count < 5) {
            $this->seed_wc_products(20);
        }
    }

    // 🚀 QUERY ENGINE
    public function get_chart_data()
    {
        if (!current_user_can('manage_woocommerce'))
            wp_send_json_error('Unauthorized');

        global $wpdb;
        $results = $wpdb->get_results(
            "SELECT report_date, SUM(order_total) as total_sales 
             FROM $this->table_name 
             WHERE report_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY report_date 
             ORDER BY report_date ASC"
        );
        wp_send_json_success($results);
    }

    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'woospeed') === false)
            return;
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    }

    // 📟 VISTA DASHBOARD
    public function render_dashboard()
    {
        global $wpdb;
        $start_time = microtime(true);
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name");
        $query_time = number_format(microtime(true) - $start_time, 5);
        ?>
        <div class="wrap">
            <h1>🚀 WooSpeed Analytics Dashboard</h1>
            <div
                style="background: #fff; border-left: 4px solid #007cba; padding: 12px 15px; margin: 15px 0; box-shadow: 0 1px 1px rgba(0,0,0,0.04);">
                <p style="margin: 0; font-size: 14px; color: #3c434a;">
                    <b>Estado del Sistema:</b> Arquitectura de Tabla Plana Activa. <br>
                    <span style="display: inline-block; margin-top: 5px;">
                        📊 Ventas Procesadas: <b><?php echo number_format($total_orders); ?></b> |
                        ⚡ Tiempo de Consulta SQL: <b><?php echo $query_time; ?>s</b>
                    </span>
                </p>
            </div>

            <div
                style="margin-top: 20px; background: white; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <canvas id="speedChart" height="100"></canvas>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('speedChart').getContext('2d');
                    let speedChart = null;

                    // Función para iniciar la gráfica
                    function initChart(data) {
                        speedChart = new Chart(ctx, {
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
                            options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
                        });
                    }

                    // Función para actualizar datos (Polling)
                    function updateChart() {
                        fetch(ajaxurl + '?action=woospeed_get_data')
                            .then(res => res.json())
                            .then(response => {
                                if (!response.success) return;

                                const data = response.data;

                                if (!speedChart) {
                                    initChart(data);
                                } else {
                                    // Actualizar datos existentes
                                    speedChart.data.labels = data.map(d => d.report_date);
                                    speedChart.data.datasets[0].data = data.map(d => d.total_sales);
                                    speedChart.update('none'); // 'none' para evitar parpadeos
                                }
                            })
                            .catch(err => console.error('Error polling data:', err));
                    }

                    // 1. Carga Inicial
                    updateChart();

                    // 2. Refresco Automático (Cada 5s)
                    setInterval(updateChart, 5000);
                });
            </script>
        </div>
        <?php
    }

    // 📟 VISTA GENERADOR
    public function render_generator_page()
    {
        // 🛡️ Crear Llave de Seguridad (Nonce)
        $nonce = wp_create_nonce('woospeed_seed_nonce');
        ?>
        <div class="wrap">
            <h1>🛠️ Generador de Datos Stress-Test</h1>
            <p>Utilice estas herramientas para simular actividad de alto tráfico en la tienda.</p>

            <?php if (isset($_GET['seeded'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        ✅ Operación Completada:
                        Generados <b><?php echo esc_html($_GET['count']); ?></b> items
                        (Tipo: <?php echo esc_html($_GET['type']); ?>).
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['cleared'])): ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        🧹 Limpieza Completada:
                        Se han eliminado <b><?php echo esc_html($_GET['count']); ?></b> registros de prueba.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Barra de Progreso (Oculta por defecto) -->
            <div id="seed-progress-container"
                style="display:none; margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05);">
                <h3>⏳ Generando Datos Masivos...</h3>
                <p>Por favor no cierres esta pestaña. Procesando <span id="processed-count">0</span> de <span
                        id="total-count">0</span>...</p>
                <progress id="seed-progress" value="0" max="100" style="width: 100%; height: 30px;"></progress>
            </div>

            <div style="display: flex; gap: 20px; margin-top: 20px;">
                <!-- Card 1: Productos (Paso 1) -->
                <div
                    style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-top: 4px solid #F5B041;">
                    <h3>1. Primero: Productos Dummy</h3>
                    <p>Genera <b>20 Productos Reales</b>. Es <b>obligatorio</b> tener productos antes de simular ventas.</p>
                    <a href="<?php echo admin_url('admin.php?page=woospeed-generator&seed_action=products_20'); ?>"
                        class="button button-secondary" style="width:100%; margin-top:10px;"
                        onclick="return confirm('¿Crear 20 Productos Reales?');">
                        📦 Generar Productos (Paso 1)
                    </a>
                    <p style="font-size: 11px; color: #666; margin-top: 5px;">* Las órdenes usarán estos productos.</p>
                </div>

                <!-- Card 2: Masiva (Paso 2) -->
                <div
                    style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-top: 4px solid #007cba;">
                    <h3>2. Segundo: Carga Masiva (5k)</h3>
                    <p>Genera <b>5,000 Pedidos Reales</b> usando los productos del paso 1. <br>Refleja una carga real.</p>
                    <button id="btn-start-batch" class="button button-primary" style="width:100%; margin-top:10px;">
                        🚀 Iniciar Carga Masiva (Paso 2)
                    </button>
                    <p style="font-size: 11px; color: #666; margin-top: 5px;">* Se ejecutará en 10 tandas de 500.</p>
                </div>

                <!-- Card 3 -->
                <div
                    style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 2px rgba(0,0,0,.05);">
                    <h3>3. Test Rápido (50)</h3>
                    <p>Prueba rápida de sincronización con 50 pedidos (opcional).</p>
                    <a href="<?php echo admin_url('admin.php?page=woospeed-generator&seed_action=orders_50'); ?>"
                        class="button button-secondary" onclick="return confirm('¿Crear 50 Pedidos Reales?');">
                        🛒 Generar 50 Pedidos
                    </a>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const btn = document.getElementById('btn-start-batch');
                    const progressContainer = document.getElementById('seed-progress-container');
                    const progressBar = document.getElementById('seed-progress');
                    const processedSpan = document.getElementById('processed-count');
                    const totalSpan = document.getElementById('total-count');

                    // 🛡️ Definir la llave de seguridad para JS
                    const securityNonce = "<?php echo $nonce; ?>";

                    const TOTAL_ORDERS = 5000;
                    const BATCH_SIZE = 500;
                    let processed = 0;

                    btn.addEventListener('click', function () {
                        if (!confirm('Esto generará 5,000 pedidos reales. ¿Continuar?')) return;

                        btn.disabled = true;
                        progressContainer.style.display = 'block';
                        processed = 0;
                        totalSpan.innerText = TOTAL_ORDERS;
                        progressBar.value = 0;

                        processBatch();
                    });

                    function processBatch() {
                        if (processed >= TOTAL_ORDERS) {
                            alert('✅ ¡Proceso Terminado! 5,000 Pedidos Generados.');
                            window.location.reload();
                            return;
                        }

                        const data = new FormData();
                        data.append('action', 'woospeed_seed_batch');
                        data.append('batch_size', BATCH_SIZE);
                        data.append('security', securityNonce); // 🛡️ Enviamos la llave al servidor

                        fetch(ajaxurl, {
                            method: 'POST',
                            body: data
                        })
                            .then(res => res.json())
                            .then(response => {
                                if (response.success) {
                                    processed += BATCH_SIZE;
                                    const percent = Math.min(100, (processed / TOTAL_ORDERS) * 100);
                                    progressBar.value = percent;
                                    processedSpan.innerText = Math.min(processed, TOTAL_ORDERS);

                                    // Recursión
                                    processBatch();
                                } else {
                                    alert('Error en el proceso: ' + response.data);
                                    btn.disabled = false;
                                }
                            })
                            .catch(err => {
                                alert('Error de red. Intenta de nuevo.');
                                btn.disabled = false;
                            });
                    }
                });
            </script>

            <div style="margin-top: 20px;">
                <p><i>Nota: Las órdenes reales aparecerán en "WooCommerce > Pedidos" y se sincronizarán automáticamente con el
                        Dashboard de Speed Analytics.</i></p>
            </div>

            <!-- Danger Zone -->
            <div
                style="margin-top: 30px; background: #fff; padding: 20px; border: 1px solid #dc3232; box-shadow: 0 1px 2px rgba(0,0,0,.05); border-left-width: 4px;">
                <h3 style="color: #dc3232; margin-top:0;">⚠️ Zona de Limpieza</h3>
                <p>Borra TODOS los datos generados por este plugin (Tabla Plana, Productos Dummy, Órdenes Dummy).</p>
                <a href="<?php echo admin_url('admin.php?page=woospeed-generator&seed_action=clear_all'); ?>"
                    class="button button-link-delete"
                    style="color: #a00; text-decoration: none; border: 1px solid #dc3232; padding: 5px 10px; border-radius: 3px;"
                    onclick="return confirm('¿ESTÁS SEGURO? Esto borrará todos los datos de prueba generados.');">
                    🗑️ BORRAR TODOS LOS DATOS DUMMY
                </a>
            </div>
        </div>
        <?php
    }
}

// Iniciar el Singleton
WooSpeed_Analytics::get_instance();
