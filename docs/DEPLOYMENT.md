# Deployment Guide for WooSpeed Analytics

This guide covers deployment strategies, best practices, and troubleshooting for production environments.

---

## Table of Contents

- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Deployment Strategies](#deployment-strategies)
- [Environment Configuration](#environment-configuration)
- [Database Migration](#database-migration)
- [Performance Tuning](#performance-tuning)
- [Monitoring](#monitoring)
- [Rollback Procedures](#rollback-procedures)
- [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Checklist

### Testing

- [ ] All PHPUnit tests passing: `composer test`
- [ ] PHPStan Level 9 passing: `composer phpstan`
- [ ] Code coverage ≥80%: `composer test:coverage`
- [ ] E2E tests passing: `npm run test:e2e`
- [ ] Manual testing on staging environment

### Code Quality

- [ ] No PHPStan errors or warnings
- [ ] PSR-12 coding standard compliant
- [ ] All PHPDoc complete
- [ ] No deprecated functions used
- [ ] No security vulnerabilities

### Documentation

- [ ] README.md updated
- [ ] CHANGELOG.md updated
- [ ] Version number updated
- [ ] Migration notes documented (if breaking changes)

### Backup

- [ ] Database backup created
- [ ] WordPress files backup created
- [ ] Migration rollback plan documented

---

## Deployment Strategies

### 1. Manual Deployment (Single Site)

#### Step 1: Prepare Release

```bash
# Run tests
composer test
composer analyze

# Create release build
composer install --no-dev --optimize-autoloader

# Create archive
git archive --format=zip --output=woospeed-analytics.zip HEAD
```

#### Step 2: Staging Deployment

1. Upload `woospeed-analytics.zip` to staging server
2. Extract to `wp-content/plugins/`
3. Activate plugin in WordPress admin
4. Run migration if needed
5. Test all functionality

#### Step 3: Production Deployment

1. **Create backup**:
   ```bash
   wp db export backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Put site in maintenance mode**:
   ```bash
   wp maintenance-mode activate
   ```

3. **Upload and extract plugin**

4. **Activate plugin**:
   ```bash
   wp plugin activate woospeed-analytics
   ```

5. **Run migration** (if needed):
   - Visit `/wp-admin/admin.php?page=woospeed-migration`
   - Click "Start Migration"
   - Wait for completion

6. **Verify deployment**:
   ```bash
   wp plugin list | grep woospeed
   ```

7. **Disable maintenance mode**:
   ```bash
   wp maintenance-mode deactivate
   ```

---

### 2. Git-Based Deployment

#### Step 1: Tag Release

```bash
# Update version in woospeed-analytics.php
# Update CHANGELOG.md

git add .
git commit -m "chore: release v3.0.1"

git tag -a v3.0.1 -m "Release v3.0.1"
git push origin main --tags
```

#### Step 2: Deploy (using WP-CLI)

```bash
#!/bin/bash
# deploy.sh

SITE_PATH="/var/www/html"
BACKUP_DIR="/var/backups/wordpress"

# Backup
wp db export "$BACKUP_DIR/db_$(date +%Y%m%d).sql" --path=$SITE_PATH

# Pull latest
cd $SITE_PATH/wp-content/plugins/woospeed-analytics
git pull origin main
git checkout $1

# Install dependencies
composer install --no-dev --optimize-autoloader

# Flush cache
wp cache flush --path=$SITE_PATH

echo "Deployment complete!"
```

Usage:
```bash
./deploy.sh v3.0.1
```

---

### 3. CI/CD Deployment (GitHub Actions)

The `.github/workflows/ci.yml` includes automated deployment. Configure:

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  release:
    types: [published]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Deploy to production
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/html/wp-content/plugins/woospeed-analytics
            git pull origin main
            composer install --no-dev
            wp plugin activate woospeed-analytics --path=/var/www/html
            wp cache flush --path=/var/www/html
```

---

## Environment Configuration

### Minimum Requirements for Production

```php
// wp-config.php requirements
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// MySQL configuration
// max_allowed_packet = 64M
// innodb_buffer_pool_size = 256M (for 100k+ orders)
```

### Server Configuration

#### Apache (.htaccess)

```apache
# Enable GZIP compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# PHP settings
<IfModule mod_php8.c>
    php_value memory_limit 256M
    php_value max_execution_time 300
</IfModule>
```

#### Nginx

```nginx
# nginx.conf for woospeed-analytics
location ~* wp-content/plugins/woospeed-analytics/assets/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

location ~* ^/wp-admin/admin\.php\?page=woospeed {
    fastcgi_read_timeout 300;
}
```

---

## Database Migration

### Large Dataset Migration (>10k orders)

For large datasets, use batch processing:

```php
// Custom migration script
add_action('woospeed_manual_migration', function() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $repository = new WooSpeed_Repository();
    $batch_size = 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    $orders = wc_get_orders([
        'limit' => $batch_size,
        'offset' => $offset,
        'status' => ['completed', 'processing'],
        'orderby' => 'ID',
        'order' => 'ASC'
    ]);
    
    foreach ($orders as $order) {
        // Sync order
        do_action('woospeed_sync_order', $order->get_id());
    }
    
    if (count($orders) === $batch_size) {
        // Continue with next batch
        $next_offset = $offset + $batch_size;
        wp_redirect(admin_url("admin.php?page=woospeed-migration&offset=$next_offset"));
        exit;
    }
    
    // Migration complete
    wp_redirect(admin_url('admin.php?page=woospeed-migration&complete=true'));
    exit;
});
```

### Migration Performance Tips

1. **Increase PHP timeout** during migration:
   ```php
   set_time_limit(0);
   ```

2. **Disable emails** during migration:
   ```php
   add_filter('woocommerce_email_enabled', '__return_false');
   ```

3. **Use WP-CLI** for fastest migration:
   ```bash
   wp eval 'foreach (wc_get_orders(["limit" => -1]) as $order) { do_action("woospeed_sync_order", $order->get_id()); }'
   ```

---

## Performance Tuning

### Database Indexes

Custom tables already have optimal indexes:

```sql
-- Verify indexes
SHOW INDEX FROM wp_wc_speed_reports;
SHOW INDEX FROM wp_wc_speed_order_items;
```

Expected output:
```
Table | Key_name     | Column_name | Cardinality
------+--------------+-------------+------------
reports | PRIMARY      | id          | High
reports | order_id     | order_id    | High
reports | report_date  | report_date | Medium
items   | PRIMARY      | id          | High
items   | order_id     | order_id    | High
items   | product_id   | product_id  | Medium
items   | report_date  | report_date | Medium
```

### Query Optimization

Monitoring slow queries:

```bash
# Enable slow query log
echo "SET GLOBAL slow_query_log = 'ON';" | mysql
echo "SET GLOBAL long_query_time = 1;" | mysql
```

### Caching Strategy

```php
// Add to wp-config.php
define('WP_CACHE', true);

// Install object cache drop-in
wp plugin install redis-cache --activate

// Configure Redis
define('WP_REDIS_HOST', '127.0.0.1');
define('WP_REDIS_PORT', 6379);
```

---

## Monitoring

### Health Checks

Create a health check endpoint:

```php
// Add to functions.php or custom plugin
add_action('rest_api_init', function() {
    register_rest_route('woospeed/v1', '/health', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            
            $reports_table = $wpdb->prefix . 'wc_speed_reports';
            $items_table = $wpdb->prefix . 'wc_speed_order_items';
            
            $reports_exists = $wpdb->get_var("SHOW TABLES LIKE '$reports_table'");
            $items_exists = $wpdb->get_var("SHOW TABLES LIKE '$items_table'");
            
            return [
                'status' => 'healthy',
                'tables' => [
                    'reports' => (bool)$reports_exists,
                    'items' => (bool)$items_exists
                ],
                'version' => WS_VERSION
            ];
        },
        'permission_callback' => '__return_true'
    ]);
});
```

Check health:
```bash
curl https://example.com/wp-json/woospeed/v1/health
```

### Error Logging

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Monitor logs
tail -f wp-content/debug.log | grep WooSpeed
```

### Performance Monitoring

Install Query Monitor plugin:
```bash
wp plugin install query-monitor --activate
```

Key metrics to watch:
- Dashboard load time: < 1s
- Database queries: < 10
- Memory usage: < 64MB
- AJAX response time: < 500ms

---

## Rollback Procedures

### Emergency Rollback

```bash
# 1. Restore database
wp db import backup_latest.sql

# 2. Revert plugin version
cd wp-content/plugins/woospeed-analytics
git checkout previous_version_tag

# 3. Clear cache
wp cache flush

# 4. Verify
wp plugin list | grep woospeed
```

### Partial Rollback (Tables Only)

```sql
-- Drop custom tables
DROP TABLE IF EXISTS wp_wc_speed_reports;
DROP TABLE IF EXISTS wp_wc_speed_order_items;

-- Recreate with previous schema
-- (Use migration from previous version)
```

---

## Troubleshooting

### Issue: Migration Timeout

**Symptoms**: Migration stops after X orders

**Solutions**:
```bash
# Increase PHP timeout
echo "max_execution_time = 300" >> php.ini

# Use WP-CLI instead
wp eval 'echo "Starting migration...";'

# Process in smaller batches
# Modify batch size in code to 25 instead of 50
```

### Issue: Dashboard Shows No Data

**Symptoms**: Dashboard loads but shows zeros

**Diagnosis**:
```bash
# Check if tables exist
wp db query "SHOW TABLES LIKE 'wp_wc_speed_%'"
wp db query "SELECT COUNT(*) FROM wp_wc_speed_reports"

# Check if migration ran
wp option get woospeed_migration_status
```

**Solutions**:
- Run migration again
- Check for data in tables
- Verify hooks are firing: `add_action('woocommerce_order_status_completed', ...)`

### Issue: Chart Not Rendering

**Symptoms**: Chart canvas is blank

**Diagnosis**:
```javascript
// Check browser console for errors
// Verify Chart.js is loaded
console.log(typeof Chart); // Should be 'function'

// Verify data is present
console.log(woospeed_dashboard_vars);
```

**Solutions**:
- Clear browser cache
- Check for JavaScript conflicts
- Verify AJAX responses

### Issue: Slow Queries

**Symptoms**: Dashboard takes > 5s to load

**Diagnosis**:
```sql
-- Enable query log
SET GLOBAL general_log = 'ON';
SET GLOBAL log_output = 'TABLE';

-- View queries
SELECT * FROM mysql.general_log 
WHERE argument LIKE '%wc_speed_%' 
ORDER BY event_time DESC 
LIMIT 20;
```

**Solutions**:
- Add missing indexes
- Optimize complex queries
- Enable query caching

---

## Post-Deployment

### Verification Steps

1. **Check plugin status**:
   ```bash
   wp plugin list | grep woospeed
   ```

2. **Verify tables**:
   ```bash
   wp db query "SHOW TABLES LIKE 'wp_wc_speed_%'"
   ```

3. **Test dashboard**:
   - Visit `/wp-admin/admin.php?page=woospeed-dashboard`
   - Verify KPI cards load
   - Check charts render
   - Test date picker

4. **Check logs**:
   ```bash
   tail -f wp-content/debug.log | grep -i woospeed
   ```

5. **Monitor performance**:
   - Load time: < 1s
   - Queries: < 10
   - Memory: < 64MB

---

## Support

For deployment issues:
- **Documentation**: [README.md](../README.md)
- **Issues**: [GitHub Issues](https://github.com/carlosindriago/woospeed-analytics/issues)
- **Email**: support@carlosindriago.com

---

**Last updated**: 2026-01-24
