<?php
/**
 * PHPStan Bootstrap for WooSpeed Analytics
 *
 * This file loads WordPress and WooCommerce function stubs for static analysis.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

// If WordPress is available in the test environment, load it
$tests_dir = getenv('WP_TESTS_DIR');

if ($tests_dir && file_exists($tests_dir . '/includes/bootstrap.php')) {
    // Load actual WordPress test environment
    require_once $tests_dir . '/includes/functions.php';
    return;
}

// Define plugin constants for static analysis
if (!defined('WS_PLUGIN_DIR')) {
    define('WS_PLUGIN_DIR', __DIR__ . '/');
}

if (!defined('WS_PLUGIN_URL')) {
    define('WS_PLUGIN_URL', 'http://example.com/wp-content/plugins/woospeed-analytics/');
}

if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/html/');
}

if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

// Otherwise, define critical WordPress/WooCommerce functions as stubs

if (!function_exists('wp_die')) {
    /**
     * Kill WordPress execution and display HTML page with error message.
     *
     * @param string|string[] $message
     * @param string $title
     * @param int|string[] $args
     * @return void
     */
    function wp_die($message, $title = '', $args = []): void
    {
        // Stub for static analysis
    }
}

if (!function_exists('sanitize_text_field')) {
    /**
     * Sanitizes a string from user input or from the database.
     *
     * @param string $str
     * @return string
     */
    function sanitize_text_field(string $str): string
    {
        return $str;
    }
}

if (!function_exists('esc_url')) {
    /**
     * Escapes a URL for use in HTML attributes.
     *
     * @param string $url
     * @return string
     */
    function esc_url(string $url): string
    {
        return $url;
    }
}

if (!function_exists('esc_html__')) {
    /**
     * Retrieve the translation of $text and escapes it for safe use in HTML output.
     *
     * @param string $text
     * @param string $domain
     * @return string
     */
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('__')) {
    /**
     * Retrieve the translation of $text.
     *
     * @param string $text
     * @param string $domain
     * @return string
     */
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('_e')) {
    /**
     * Display translated text.
     *
     * @param string $text
     * @param string $domain
     * @return void
     */
    function _e(string $text, string $domain = 'default'): void
    {
        echo $text;
    }
}

if (!function_exists('admin_url')) {
    /**
     * Retrieves the URL to the admin area for the current site.
     *
     * @param string $path
     * @param string $scheme
     * @return string
     */
    function admin_url(string $path = '', string $scheme = 'admin'): string
    {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_redirect')) {
    /**
     * Redirects to another page.
     *
     * @param string $location
     * @param int $status
     * @return bool
     */
    function wp_redirect(string $location, int $status = 302): bool
    {
        return true;
    }
}

if (!function_exists('current_time')) {
    /**
     * Retrieves the current time based on specified type.
     *
     * @param string $type
     * @return int|string
     */
    function current_time(string $type = 'mysql'): int|string
    {
        if ($type === 'mysql') {
            return date('Y-m-d H:i:s');
        }
        return time();
    }
}

if (!function_exists('get_option')) {
    /**
     * Retrieves an option value based on an option name.
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    function get_option(string $option, $default = false): mixed
    {
        return $default;
    }
}

if (!function_exists('update_option')) {
    /**
     * Updates the value of an option that was already added.
     *
     * @param string $option
     * @param mixed $value
     * @return bool
     */
    function update_option(string $option, mixed $value): bool
    {
        return true;
    }
}

if (!function_exists('delete_option')) {
    /**
     * Deletes an option by name.
     *
     * @param string $option
     * @return bool
     */
    function delete_option(string $option): bool
    {
        return true;
    }
}

if (!function_exists('check_ajax_referer')) {
    /**
     * Verifies the AJAX request to prevent processing requests external of the blog.
     *
     * @param string|int $action
     * @param false|string $query_arg
     * @param bool $die
     * @return false|int
     */
    function check_ajax_referer(string|int $action = -1, false|string $query_arg = false, bool $die = true): false|int
    {
        return 1;
    }
}

if (!function_exists('current_user_can')) {
    /**
     * Returns whether the current user has the specified capability.
     *
     * @param string $capability
     * @param mixed ...$args
     * @return bool
     */
    function current_user_can(string $capability, ...$args): bool
    {
        return true;
    }
}

if (!function_exists('wp_send_json_success')) {
    /**
     * Send a JSON response back to an Ajax request, indicating success.
     *
     * @param mixed $data
     * @param int $status_code
     * @param int $options
     * @return void
     */
    function wp_send_json_success(mixed $data = null, int $status_code = null, int $options = 0): void
    {
        // Stub for static analysis
    }
}

if (!function_exists('wp_send_json_error')) {
    /**
     * Send a JSON response back to an Ajax request, indicating failure.
     *
     * @param mixed $data
     * @param int $status_code
     * @param int $options
     * @return void
     */
    function wp_send_json_error(mixed $data = null, int $status_code = null, int $options = 0): void
    {
        // Stub for static analysis
    }
}

if (!function_exists('wp_verify_nonce')) {
    /**
     * Verifies that a correct security nonce was used with time limit.
     *
     * @param string $nonce
     * @param string|int $action
     * @return false|int
     */
    function wp_verify_nonce(string $nonce, string|int $action = -1): false|int
    {
        return 1;
    }
}

if (!function_exists('wp_create_nonce')) {
    /**
     * Creates a cryptographic token tied to a specific action, user, user session, and time window.
     *
     * @param string|int $action
     * @return string
     */
    function wp_create_nonce(string|int $action = -1): string
    {
        return 'mock_nonce_12345';
    }
}

if (!function_exists('load_plugin_textdomain')) {
    /**
     * Loads the plugin's translated strings.
     *
     * @param string $domain
     * @param false|string $deprecated
     * @param false|string $plugin_rel_path
     * @return bool
     */
    function load_plugin_textdomain(string $domain, false|string $deprecated = false, false|string $plugin_rel_path = false): bool
    {
        return true;
    }
}

if (!function_exists('plugin_basename')) {
    /**
     * Gets the basename of a plugin.
     *
     * @param string $file
     * @return string
     */
    function plugin_basename(string $file): string
    {
        return basename($file);
    }
}

if (!function_exists('add_action')) {
    /**
     * Hooks a function on to a specific action.
     *
     * @param string $hook_name
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     * @return true
     */
    function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return true;
    }
}

if (!function_exists('add_filter')) {
    /**
     * Hooks a function or method to a specific filter action.
     *
     * @param string $hook_name
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     * @return true
     */
    function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return true;
    }
}

if (!function_exists('wp_delete_post')) {
    /**
     * Trash or delete a post or page.
     *
     * @param int  $postid       Post ID.
     * @param bool $force_delete Whether to bypass trash and force deletion.
     * @return WP_Post|false|null Post data on success, false or null on failure.
     */
    function wp_delete_post(int $postid, bool $force_delete = false): mixed
    {
        return null;
    }
}

// WooCommerce stubs

if (!function_exists('wc_get_order')) {
    /**
     * Get an order object.
     *
     * @param int|WC_Order $order Order ID or object.
     * @return WC_Order|false
     */
    function wc_get_order(int|object $order): WC_Order|false
    {
        return false;
    }
}

if (!function_exists('wc_get_orders')) {
    /**
     * Standard way to retrieve orders based on arguments.
     *
     * @param array $args
     * @return WC_Order[]|int[]
     */
    function wc_get_orders(array $args = []): array
    {
        return [];
    }
}

if (!function_exists('wc_create_order')) {
    /**
     * Create a new order programmatically.
     *
     * @param array $args
     * @return WC_Order|false
     */
    function wc_create_order(array $args = []): mixed
    {
        return false;
    }
}

if (!function_exists('wc_get_products')) {
    /**
     * Standard way to retrieve products based on arguments.
     *
     * @param array $args
     * @return WC_Product[]|int[]
     */
    function wc_get_products(array $args = []): array
    {
        return [];
    }
}

if (!class_exists('WC_Order')) {
    /**
     * Mock WC_Order class.
     */
    class WC_Order
    {
        public function get_id(): int
        {
            return 0;
        }

        public function get_total(): float
        {
            return 0.0;
        }

        public function get_date_created(): ?DateTime
        {
            return new DateTime();
        }

        public function get_items(): array
        {
            return [];
        }

        public function set_date_created(string $date): void
        {
        }

        public function set_date_completed(string $date): void
        {
        }

        public function set_date_paid(string $date): void
        {
        }

        public function add_product(WC_Product $product, int $qty = 1): bool
        {
            return true;
        }

        public function set_address(array $data, string $type = 'billing'): void
        {
        }

        public function calculate_totals(): void
        {
        }

        public function add_meta_data(string $key, mixed $value, bool $unique = false): void
        {
        }

        public function update_status(string $status, string $note = ''): bool
        {
            return true;
        }
    }
}

if (!class_exists('WC_Product')) {
    /**
     * Mock WC_Product class.
     */
    class WC_Product
    {
        public function get_id(): int
        {
            return 0;
        }
    }
}

if (!class_exists('WC_Product_Simple')) {
    /**
     * Mock WC_Product_Simple class.
     */
    class WC_Product_Simple extends WC_Product
    {
        public function set_name(string $name): void
        {
        }

        public function set_regular_price(float $price): void
        {
        }

        public function set_description(string $description): void
        {
        }

        public function set_short_description(string $description): void
        {
        }

        public function set_status(string $status): void
        {
        }

        public function add_meta_data(string $key, mixed $value, bool $unique = false): void
        {
        }

        public function save(): int
        {
            return 0;
        }
    }
}
