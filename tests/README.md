# Testing - WooSpeed Analytics

## Overview

This test suite uses PHPUnit to test WooSpeed Analytics functionality. Tests are organized by component and focus on critical functionality.

## Test Structure

```
tests/
├── bootstrap.php          # Loads WordPress and plugin
├── phpunit.xml            # PHPUnit configuration
└── unit/
    └── RepositoryTest.php # Repository unit tests
```

## Prerequisites

1. **PHPUnit 10.x**
   ```bash
   composer require --dev phpunit/phpunit:^10.0
   ```

2. **WordPress Test Suite**
   ```bash
   # Install WordPress test library
   bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```

## Running Tests

### Run all tests:
```bash
cd /path/to/wp-content/plugins/woospeed-analytics
phpunit
```

### Run specific test suite:
```bash
phpunit --testsuite unit
```

### Run specific test class:
```bash
phpunit tests/unit/RepositoryTest.php
```

### Run specific test method:
```bash
phpunit --filter test_save_report
```

### Generate coverage report:
```bash
phpunit --coverage-html coverage/html
```

## Test Coverage

Current coverage targets:
- **Repository**: 90%+ (critical data layer)
- **Admin Hooks**: 70%+ (WooCommerce integration)
- **API**: 70%+ (AJAX endpoints)
- **Seeder**: 50%+ (data generation)

## Current Tests

### RepositoryTest.php

Tests the core data layer:

- ✅ `test_create_tables` - Verifies table creation
- ✅ `test_save_report` - Tests report insertion
- ✅ `test_save_report_updates_existing` - Tests upsert logic
- ✅ `test_save_items` - Tests order items insertion
- ✅ `test_delete_order_data` - Tests data deletion
- ✅ `test_get_kpis` - Tests KPI calculation
- ✅ `test_get_kpis_empty_database` - Edge case testing
- ✅ `test_get_chart_data` - Tests chart data retrieval
- ✅ `test_get_top_products` - Tests leaderboard queries
- ✅ `test_has_items` - Tests item existence check
- ✅ `test_clean_dummy_tables` - Tests cleanup functionality
- ✅ `test_get_extreme_days` - Tests best/worst day logic
- ✅ `test_get_weekday_sales` - Tests weekday aggregation
- ✅ `test_batch_insert_reports` - Tests bulk insert
- ✅ `test_batch_insert_reports_empty_array` - Edge case testing

**Total: 15 tests covering all critical Repository methods**

## Continuous Integration

Tests should run automatically:
- Before each commit
- On pull requests
- Before deployment to production

## Writing New Tests

When adding new functionality:

1. Create a test file in `tests/unit/`
2. Name it `ClassNameTest.php`
3. Extend `PHPUnit\Framework\TestCase`
4. Use `setUp()` for test initialization
5. Use descriptive test names: `test_<method>_<scenario>`

Example:
```php
public function test_get_kpis_with_multiple_orders(): void
{
    // Arrange
    $this->insert_test_data();

    // Act
    $kpis = $this->repository->get_kpis('2026-01-01', '2026-01-31');

    // Assert
    $this->assertEquals(300, $kpis->revenue);
}
```

## Troubleshooting

### "WordPress tests not found"
```bash
# Install WordPress test suite
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

### "Class not found"
```bash
# Rebuild autoload
composer dump-autoload
```

### Database connection errors
```bash
# Check MySQL is running
systemctl status mysql

# Verify wp-tests-config.php exists
ls -la /tmp/wordpress-tests-lib/wp-tests-config.php
```

## Best Practices

1. **Isolation**: Each test should be independent
2. **Cleanup**: Use `setUp()` to clean test data
3. **One assertion per test** (when possible)
4. **Descriptive names**: Test names should document what they test
5. **Test edges**: Empty arrays, null values, boundary conditions
6. **Mock external dependencies**: Don't rely on real APIs

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Test Suite](https://make.wordpress.org/core/handbook/testing/automated-testing/)
- [WooCommerce Testing](https://developer.woocommerce.com/docs/unit-testing/)
