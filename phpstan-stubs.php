<?php
/**
 * Additional PHPStan Stubs for WordPress/WooCommerce
 *
 * This file provides type definitions for WordPress global functions and classes
 * that don't have proper reflection data available.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

declare(strict_types=1);

/**
 * WordPress Database class stub
 */
class wpdb {
    public $prefix = 'wp_';
    public $posts;
    public $postmeta;
    public $options;
    public $terms;
    public $term_relationships;
    public $term_taxonomy;
    public $term;

    /**
     * Prepares a SQL query for safe execution.
     *
     * @param string $query
     * @param mixed ...$args
     * @return string
     */
    public function prepare(string $query, ...$args): string {
        return '';
    }

    /**
     * Perform a MySQL database query.
     *
     * @param string $query
     * @return int|bool
     */
    public function query(string $query): int|bool {
        return false;
    }

    /**
     * Retrieve one variable from the database.
     *
     * @param string $query
     * @param int $x
     * @param int $offset
     * @return string|null
     */
    public function get_var(string $query, int $x = 0, int $offset = 0): ?string {
        return null;
    }

    /**
     * Retrieve one row from the database.
     *
     * @param string $query
     * @param string $output
     * @param int $y
     * @return object|void
     */
    public function get_row(string $query, string $output = OBJECT, int $y = 0): object|null {
        return null;
    }

    /**
     * Retrieve an entire result set from the database.
     *
     * @param string $query
     * @param string $output
     * @return array
     */
    public function get_results(string $query, string $output = OBJECT): array {
        return [];
    }

    /**
     * Retrieve one column from the database.
     *
     * @param string $query
     * @param int $x
     * @return array
     */
    public function get_col(string $query, int $x = 0): array {
        return [];
    }

    /**
     * Insert a row into a table.
     *
     * @param string $table
     * @param array $data
     * @param string|string[] $format
     * @return int|bool
     */
    public function insert(string $table, array $data, $format = null): int|bool {
        return false;
    }

    /**
     * Update a row in the table.
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @param string|string[] $format
     * @param string|string[] $where_format
     * @return int|bool
     */
    public function update(string $table, array $data, array $where, $format = null, $where_format = null): int|bool {
        return false;
    }

    /**
     * Delete a row in the table.
     *
     * @param string $table
     * @param array $where
     * @param string|string[] $where_format
     * @return int|bool
     */
    public function delete(string $table, array $where, $where_format = null): int|bool {
        return false;
    }

    /**
     * Retrieve the name of the charset that's being used by the database.
     *
     * @return string
     */
    public function get_charset_collate(): string {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }

    /**
     * Get the last error from the database.
     *
     * @return string
     */
    public function last_error: string = '';
}

/**
 * Stub for register_activation_hook
 *
 * @param string $file
 * @param callable $callback
 * @return bool
 */
function register_activation_hook(string $file, callable $callback): bool {
    return true;
}

/**
 * Stub for register_deactivation_hook
 *
 * @param string $file
 * @param callable $callback
 * @return bool
 */
function register_deactivation_hook(string $file, callable $callback): bool {
    return true;
}

/**
 * Stub for add_menu_page
 *
 * @param string $page_title
 * @param string $menu_title
 * @param string $capability
 * @param string $menu_slug
 * @param callable $callback
 * @param string $icon_url
 * @param int $position
 * @return string
 */
function add_menu_page(
    string $page_title,
    string $menu_title,
    string $capability,
    string $menu_slug,
    callable $callback = null,
    string $icon_url = '',
    int $position = null
): string {
    return '';
}

/**
 * Stub for add_submenu_page
 *
 * @param string $parent_slug
 * @param string $page_title
 * @param string $menu_title
 * @param string $capability
 * @param string $menu_slug
 * @param callable $callback
 * @return string
 */
function add_submenu_page(
    string $parent_slug,
    string $page_title,
    string $menu_title,
    string $capability,
    string $menu_slug,
    callable $callback = null
): string {
    return '';
}

/**
 * Stub for wp_enqueue_style
 *
 * @param string $handle
 * @param string $src
 * @param string[] $deps
 * @param string|bool|null $ver
 * @param string $media
 * @return void
 */
function wp_enqueue_style(
    string $handle,
    string $src = '',
    array $deps = [],
    $ver = false,
    string $media = 'all'
): void {
}

/**
 * Stub for wp_enqueue_script
 *
 * @param string $handle
 * @param string $src
 * @param string[] $deps
 * @param string|bool|null $ver
 * @param bool $in_footer
 * @return void
 */
function wp_enqueue_script(
    string $handle,
    string $src = '',
    array $deps = [],
    $ver = false,
    bool $in_footer = false
): void {
}

/**
 * Stub for wp_localize_script
 *
 * @param string $handle
 * @param string $object_name
 * @param array $data
 * @return bool
 */
function wp_localize_script(string $handle, string $object_name, array $data): bool {
    return true;
}

/**
 * Stub for plugins_url
 *
 * @param string $path
 * @param string $plugin
 * @return string
 */
function plugins_url(string $path = '', string $plugin = ''): string {
    return '';
}

/**
 * Stub for plugin_dir_path
 *
 * @param string $file
 * @return string
 */
function plugin_dir_path(string $file): string {
    return '';
}

/**
 * Stub for plugin_dir_url
 *
 * @param string $file
 * @return string
 */
function plugin_dir_url(string $file): string {
    return '';
}

/**
 * Stub for is_admin
 *
 * @return bool
 */
function is_admin(): bool {
    return false;
}

/**
 * Stub for wp_localize_script (alias for consistency)
 */
function wp_localize_script(string $handle, string $object_name, array $l10n): bool {
    return true;
}
