#!/usr/bin/env bash

if [ $# -lt 3 ]; then
    echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/*$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1";
    else
        echo "&gt; Neither curl nor wget found. Exiting."
        exit 1
    fi
}

mkdir -p $WP_TESTS_DIR
mkdir -p $WP_CORE_DIR

# Download WordPress
if [ -d $WP_CORE_DIR ]; then
    echo "WordPress already downloaded in $WP_CORE_DIR"
else
    echo "Downloading WordPress to $WP_CORE_DIR"
    download https://wordpress.org/wordpress-$WP_VERSION.tar.gz $TMPDIR/wordpress.tar.gz
    tar -xzvf $TMPDIR/wordpress.tar.gz -C $TMPDIR/
    mv $TMPDIR/wordpress $WP_CORE_DIR
fi

# Download WordPress Test Suite
if [ -d $WP_TESTS_DIR ]; then
    echo "WordPress test suite already downloaded in $WP_TESTS_DIR"
else
    echo "Downloading WordPress test suite to $WP_TESTS_DIR"
    download https://github.com/WordPress/wordpress-develop/archive/refs/tags/$WP_VERSION.tar.gz $TMPDIR/wp-tests.tar.gz
    tar -xzvf $TMPDIR/wp-tests.tar.gz -C $TMPDIR/
    mv $TMPDIR/wordpress-develop-$WP_VERSION/tests/phpunit $WP_TESTS_DIR
fi

# Create wp-tests-config.php
echo "Creating wp-tests-config.php"
cat > $WP_TESTS_DIR/wp-tests-config.php <<EOF
<?php
define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

\$table_prefix = 'wp_';

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __FILE__ ) . '/vendor/yoast/phpunit-polyfills' );

define( 'WP_DEBUG', true );
define( 'WP_PHP_BINARY', 'php' );

define( 'WP_TESTS_DIR', __DIR__ );
define( 'WP_CORE_DIR', '$WP_CORE_DIR' );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '$WP_CORE_DIR' );
}

$_tests_dir = WP_TESTS_DIR;

require_once __DIR__ . '/includes/functions.php';

// Activate plugins if needed
// require_once ABSPATH . 'wp-admin/includes/plugin.php';
// activate_plugin( 'woocommerce/woocommerce.php' );

echo "WordPress test environment loaded.\n";
echo "DB: $DB_NAME@$DB_HOST\n";
echo "WordPress: $WP_VERSION\n";
EOF

# Create database if not exists
if [ "$SKIP_DB_CREATE" = "false" ]; then
    mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"
fi

echo "WordPress test suite installed successfully!"
echo "WP_TESTS_DIR: $WP_TESTS_DIR"
echo "WP_CORE_DIR: $WP_CORE_DIR"
