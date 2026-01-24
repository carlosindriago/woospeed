<?php
/**
 * WooSpeed Analytics Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('woospeed_settings', [
    'default_date_range' => 'month_to_date',
    'refresh_interval' => 30,
    'show_kpi_cards' => true,
    'show_sales_chart' => true,
    'show_weekday_chart' => true,
    'show_top_products' => true,
    'show_bottom_products' => true,
    'show_categories' => true,
    'theme' => 'auto'
]);

// Handle form submission
if (isset($_POST['woospeed_save_settings']) && wp_verify_nonce($_POST['_wpnonce'], 'woospeed_settings_nonce')) {
    $settings = [
        'default_date_range' => sanitize_text_field($_POST['default_date_range'] ?? 'month_to_date'),
        'refresh_interval' => intval($_POST['refresh_interval'] ?? 30),
        'show_kpi_cards' => isset($_POST['show_kpi_cards']),
        'show_sales_chart' => isset($_POST['show_sales_chart']),
        'show_weekday_chart' => isset($_POST['show_weekday_chart']),
        'show_top_products' => isset($_POST['show_top_products']),
        'show_bottom_products' => isset($_POST['show_bottom_products']),
        'show_categories' => isset($_POST['show_categories']),
        'theme' => sanitize_text_field($_POST['theme'] ?? 'auto')
    ];
    update_option('woospeed_settings', $settings);
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'woospeed-analytics') . '</p></div>';
}
?>

<div class="wrap woospeed-settings">
    <h1>
        <span class="dashicons dashicons-chart-bar" style="font-size: 30px; margin-right: 10px;"></span>
        <?php _e('WooSpeed Analytics Settings', 'woospeed-analytics'); ?>
    </h1>

    <form method="post" action="">
        <?php wp_nonce_field('woospeed_settings_nonce'); ?>

        <!-- General Settings -->
        <div class="woospeed-settings-section">
            <h2>
                <span class="dashicons dashicons-admin-generic"></span>
                <?php _e('General Settings', 'woospeed-analytics'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="default_date_range">
                            <?php _e('Default Date Range', 'woospeed-analytics'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="default_date_range" id="default_date_range" class="regular-text">
                            <option value="today" <?php selected($settings['default_date_range'], 'today'); ?>>
                                <?php _e('Today', 'woospeed-analytics'); ?>
                            </option>
                            <option value="yesterday" <?php selected($settings['default_date_range'], 'yesterday'); ?>>
                                <?php _e('Yesterday', 'woospeed-analytics'); ?>
                            </option>
                            <option value="week_to_date" <?php selected($settings['default_date_range'], 'week_to_date'); ?>>
                                <?php _e('Week to date', 'woospeed-analytics'); ?>
                            </option>
                            <option value="last_week" <?php selected($settings['default_date_range'], 'last_week'); ?>>
                                <?php _e('Last week', 'woospeed-analytics'); ?>
                            </option>
                            <option value="month_to_date" <?php selected($settings['default_date_range'], 'month_to_date'); ?>>
                                <?php _e('Month to date', 'woospeed-analytics'); ?>
                            </option>
                            <option value="last_month" <?php selected($settings['default_date_range'], 'last_month'); ?>>
                                <?php _e('Last month', 'woospeed-analytics'); ?>
                            </option>
                            <option value="quarter_to_date" <?php selected($settings['default_date_range'], 'quarter_to_date'); ?>>
                                <?php _e('Quarter to date', 'woospeed-analytics'); ?>
                            </option>
                            <option value="last_quarter" <?php selected($settings['default_date_range'], 'last_quarter'); ?>>
                                <?php _e('Last quarter', 'woospeed-analytics'); ?>
                            </option>
                            <option value="year_to_date" <?php selected($settings['default_date_range'], 'year_to_date'); ?>>
                                <?php _e('Year to date', 'woospeed-analytics'); ?>
                            </option>
                            <option value="last_year" <?php selected($settings['default_date_range'], 'last_year'); ?>>
                                <?php _e('Last year', 'woospeed-analytics'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e('The default date range shown when opening the dashboard.', 'woospeed-analytics'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="refresh_interval">
                            <?php _e('Auto-Refresh Interval', 'woospeed-analytics'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="refresh_interval" id="refresh_interval" class="regular-text">
                            <option value="0" <?php selected($settings['refresh_interval'], 0); ?>>
                                <?php _e('Disabled', 'woospeed-analytics'); ?>
                            </option>
                            <option value="10" <?php selected($settings['refresh_interval'], 10); ?>>10
                                <?php _e('seconds', 'woospeed-analytics'); ?>
                            </option>
                            <option value="30" <?php selected($settings['refresh_interval'], 30); ?>>30
                                <?php _e('seconds', 'woospeed-analytics'); ?>
                            </option>
                            <option value="60" <?php selected($settings['refresh_interval'], 60); ?>>1
                                <?php _e('minute', 'woospeed-analytics'); ?>
                            </option>
                            <option value="300" <?php selected($settings['refresh_interval'], 300); ?>>5
                                <?php _e('minutes', 'woospeed-analytics'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e('How often the dashboard automatically refreshes data.', 'woospeed-analytics'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Dashboard Widgets -->
        <div class="woospeed-settings-section">
            <h2>
                <span class="dashicons dashicons-screenoptions"></span>
                <?php _e('Dashboard Widgets', 'woospeed-analytics'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php _e('Visible Widgets', 'woospeed-analytics'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="show_kpi_cards" value="1" <?php checked($settings['show_kpi_cards']); ?>>
                                <?php _e('KPI Cards (Revenue, Orders, AOV, Max Order)', 'woospeed-analytics'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="show_sales_chart" value="1" <?php checked($settings['show_sales_chart']); ?>>
                                <?php _e('Sales Trend Chart', 'woospeed-analytics'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="show_weekday_chart" value="1" <?php checked($settings['show_weekday_chart']); ?>>
                                <?php _e('Sales by Day of Week Chart', 'woospeed-analytics'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="show_top_products" value="1" <?php checked($settings['show_top_products']); ?>>
                                <?php _e('Top Products Leaderboard', 'woospeed-analytics'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="show_bottom_products" value="1" <?php checked($settings['show_bottom_products']); ?>>
                                <?php _e('Least Sold Products', 'woospeed-analytics'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="show_categories" value="1" <?php checked($settings['show_categories']); ?>>
                                <?php _e('Top Categories', 'woospeed-analytics'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Appearance -->
        <div class="woospeed-settings-section">
            <h2>
                <span class="dashicons dashicons-art"></span>
                <?php _e('Appearance', 'woospeed-analytics'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="theme">
                            <?php _e('Theme', 'woospeed-analytics'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="theme" id="theme" class="regular-text">
                            <option value="auto" <?php selected($settings['theme'], 'auto'); ?>>
                                <?php _e('Auto (System)', 'woospeed-analytics'); ?>
                            </option>
                            <option value="light" <?php selected($settings['theme'], 'light'); ?>>
                                <?php _e('Light', 'woospeed-analytics'); ?>
                            </option>
                            <option value="dark" <?php selected($settings['theme'], 'dark'); ?>>
                                <?php _e('Dark', 'woospeed-analytics'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e('Dashboard color theme. Auto follows your system preference.', 'woospeed-analytics'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <input type="submit" name="woospeed_save_settings" class="button button-primary"
                value="<?php _e('Save Settings', 'woospeed-analytics'); ?>">
        </p>
    </form>

    <!-- Data Management -->
    <div class="woospeed-settings-section">
        <h2>
            <span class="dashicons dashicons-database"></span>
            <?php _e('Data Management', 'woospeed-analytics'); ?>
        </h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php _e('Sync Orders', 'woospeed-analytics'); ?>
                </th>
                <td>
                    <a href="<?php echo admin_url('admin.php?page=woospeed-migration'); ?>" class="button">
                        <?php _e('Go to Migration Tool', 'woospeed-analytics'); ?>
                    </a>
                    <p class="description">
                        <?php _e('Force sync all WooCommerce orders to WooSpeed Analytics tables.', 'woospeed-analytics'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e('Database Info', 'woospeed-analytics'); ?>
                </th>
                <td>
                    <?php
                    global $wpdb;
                    $reports_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wc_speed_reports");
                    $items_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wc_speed_order_items");
                    ?>
                    <p><strong>
                            <?php _e('Reports Table:', 'woospeed-analytics'); ?>
                        </strong>
                        <?php echo number_format($reports_count); ?>
                        <?php _e('records', 'woospeed-analytics'); ?>
                    </p>
                    <p><strong>
                            <?php _e('Items Table:', 'woospeed-analytics'); ?>
                        </strong>
                        <?php echo number_format($items_count); ?>
                        <?php _e('records', 'woospeed-analytics'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Developer Tools -->
    <div class="woospeed-settings-section ws-dev-tools">
        <h2 class="woospeed-dev-tools-header"
            onclick="document.getElementById('ws-dev-tools-content').classList.toggle('ws-hidden');">
            <span class="dashicons dashicons-code-standards"></span>
            <?php _e('Developer Tools', 'woospeed-analytics'); ?>
            <span class="dashicons dashicons-arrow-down-alt2" style="float:right;"></span>
        </h2>

        <div id="woospeed-dev-tools-content" class="woospeed-hidden">
            <p class="description" style="color: #d63638; margin-bottom: 20px;">
                ⚠️
                <?php _e('These tools are for development and testing purposes only. Use with caution in production environments.', 'woospeed-analytics'); ?>
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php _e('Generate Dummy Data', 'woospeed-analytics'); ?>
                    </th>
                    <td>
                        <form method="GET" action="<?php echo admin_url('admin.php'); ?>"
                            style="display: inline-flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="page" value="woospeed-analytics">
                            <input type="hidden" name="action" value="seed">
                            <?php wp_nonce_field('woospeed_seed_action', '_wpnonce'); ?>
                            <select name="count">
                                <option value="10">10
                                    <?php _e('orders', 'woospeed-analytics'); ?>
                                </option>
                                <option value="50">50
                                    <?php _e('orders', 'woospeed-analytics'); ?>
                                </option>
                                <option value="100" selected>100
                                    <?php _e('orders', 'woospeed-analytics'); ?>
                                </option>
                                <option value="500">500
                                    <?php _e('orders', 'woospeed-analytics'); ?>
                                </option>
                                <option value="1000">1000
                                    <?php _e('orders', 'woospeed-analytics'); ?>
                                </option>
                            </select>
                            <button type="submit" class="button button-secondary">
                                <?php _e('Generate', 'woospeed-analytics'); ?>
                            </button>
                        </form>
                        <p class="description">
                            <?php _e('Creates fake orders for testing. Orders are marked with a special meta field.', 'woospeed-analytics'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Clean Dummy Data', 'woospeed-analytics'); ?>
                    </th>
                    <td>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=woospeed-analytics&action=clean'), 'woospeed_seed_action'); ?>"
                            class="button button-secondary"
                            onclick="return confirm('<?php _e('Are you sure you want to delete all dummy data?', 'woospeed-analytics'); ?>');">
                            <?php _e('Clean Dummy Data', 'woospeed-analytics'); ?>
                        </a>
                        <p class="description">
                            <?php _e('Removes all test orders generated by WooSpeed.', 'woospeed-analytics'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
    .woospeed-settings {
        max-width: 900px;
    }

    .woospeed-settings h1 {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
    }

    .ws-settings-section {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        margin-bottom: 20px;
        padding: 0;
    }

    .ws-settings-section h2 {
        margin: 0;
        padding: 15px 20px;
        border-bottom: 1px solid #c3c4c7;
        background: #f6f7f7;
        font-size: 14px;
        font-weight: 600;
    }

    .ws-settings-section h2 .dashicons {
        margin-right: 8px;
        color: #646970;
    }

    .ws-settings-section .form-table {
        margin: 0;
        padding: 10px 20px;
    }

    .ws-settings-section .form-table th {
        padding: 20px 10px 20px 0;
        width: 200px;
    }

    .ws-settings-section .form-table td {
        padding: 15px 10px;
    }

    .ws-settings-section fieldset label {
        display: block;
        margin-bottom: 8px;
    }

    .ws-dev-tools-header {
        cursor: pointer;
        user-select: none;
    }

    .ws-dev-tools-header:hover {
        background: #f0f0f1 !important;
    }

    .ws-hidden {
        display: none;
    }

    .ws-dev-tools .description {
        margin-top: 8px;
    }
</style>

<script>
    // Auto-hide dev tools content on load
    document.addEventListener('DOMContentLoaded', function () {
        const devContent = document.getElementById('ws-dev-tools-content');
        if (devContent) {
            devContent.classList.add('ws-hidden');
        }
    });
</script>