# WooSpeed Analytics 🚀

<div align="center">

**High-performance WooCommerce analytics engine with Flat Table architecture**

[![CI/CD](https://github.com/carlosindriago/woospeed/actions/workflows/ci.yml/badge.svg)](https://github.com/carlosindriago/woospeed/actions/workflows/ci.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%209-brightgreen)](https://phpstan.org/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-10.x-blue)](https://phpunit.de/)
[![License](https://img.shields.io/badge/license-GPL%20v2.0-blue)](LICENSE)

**0.01s report generation** | **Flat Table architecture** | **WordPress VIP ready**

</div>

---

## 📋 Table of Contents

- [Features](#-features)
- [Performance](#-performance)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [Architecture](#-architecture)
- [Development](#-development)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [Changelog](#changelog)
- [License](#license)

---

## ✨ Features

### Dashboard
- **Real-time KPIs**: Revenue, Orders, AOV, Max Order
- **Advanced Date Range Picker**: Presets + Custom ranges
- **Sales Trend Chart**: Line chart with daily granularity
- **Weekday Analysis**: Bar chart showing sales by day of week
- **Best/Worst Days**: Identify peak and low performing days
- **Top Products Leaderboard**: Best-selling products
- **Bottom Products**: Underperforming products
- **Category Performance**: Revenue by product category

### Performance
- **0.01s query time** for any date range (vs 30s+ with wp_postmeta)
- **Flat Table Architecture**: Denormalized data for instant reads
- **Raw SQL**: No ORM overhead, optimized queries
- **Batch Processing**: Efficient order migration
- **No N+1 Queries**: All data fetched in single queries

### Developer Experience
- **PHP 8.1+ Strict Types**: Type-safe codebase
- **ES6+ JavaScript**: Modern classes with private fields
- **PHPStan Level 9**: Maximum static analysis strictness
- **PHPUnit Tests**: Comprehensive unit and integration tests
- **PSR-12 Autoloading**: Standard PHP structure

---

## ⚡ Performance

| Metric | WooSpeed | WooCommerce Native | Improvement |
|--------|----------|-------------------|-------------|
| Dashboard load | 0.01s | 30s+ | **3000x faster** |
| Database queries | 3 | 500+ | **99% reduction** |
| Memory usage | 2MB | 128MB | **98% reduction** |
| Date range flexibility | Any range | Limited presets | Unlimited |

**Benchmark**: 100,000 orders over 365 days

---

## 📦 Requirements

- **PHP**: 8.1 or higher
- **WordPress**: 6.0 or higher
- **WooCommerce**: 7.0 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.2 or higher
- **Memory**: 128MB minimum (256MB recommended)

---

## 🚀 Installation

### Method 1: WordPress Admin (Recommended)

1. Download the plugin ZIP from the [latest release](https://github.com/carlosindriago/woospeed-analytics/releases)
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the ZIP file
4. Activate the plugin
5. Follow the setup wizard

### Method 2: Manual Upload

```bash
cd wp-content/plugins
git clone https://github.com/carlosindriago/woospeed-analytics.git
cd woospeed-analytics
composer install --no-dev
```

Then activate in WordPress admin.

### Method 3: WP-CLI

```bash
wp plugin install https://github.com/carlosindriago/woospeed-analytics/archive/master.zip --activate
```

---

## 🎯 Quick Start

### First Time Setup

1. **Activate the plugin** - Custom tables are created automatically
2. **Migration Notice** - If you have existing orders, you'll see a migration notice
3. **Run Migration** - Click "Start Migration" to sync existing orders
4. **View Dashboard** - Go to **WooSpeed Analytics** in WordPress admin

### Generate Test Data (Optional)

For testing purposes, you can generate dummy orders:

1. Go to **Settings > Data Generator**
2. Click **"Generate Products (Step 1)"** to create 20 dummy products
3. Click **"Start Mass Load (Step 2)"** to generate 5,000 test orders
4. View the analytics dashboard with realistic data

⚠️ **Warning**: Test data is marked with `_woospeed_dummy` meta and can be deleted safely.

---

## 🏗️ Architecture

### Flat Table Pattern

Instead of querying complex `wp_postmeta` structures, we use denormalized flat tables:

```sql
-- wc_speed_reports (one row per order)
CREATE TABLE wc_speed_reports (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    order_id bigint(20) NOT NULL,
    order_total decimal(10,2) NOT NULL,
    report_date date NOT NULL,
    PRIMARY KEY  (id),
    UNIQUE KEY order_id (order_id),
    KEY report_date (report_date)
);

-- wc_speed_order_items (one row per product per order)
CREATE TABLE wc_speed_order_items (
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
);
```

---

## 💻 Development

### Environment Setup

```bash
# Clone repository
git clone https://github.com/carlosindriago/woospeed-analytics.git
cd woospeed-analytics

# Install dependencies
composer install

# Run tests
composer test
```

### Code Standards

- **PHPStan Level 9**: Maximum static analysis
- **PSR-12**: Coding standard
- **PHP 8.1+ Strict Types**: Type safety enforced

---

## 🧪 Testing

```bash
# Unit tests
composer test

# With coverage
composer test:coverage
```

---

## 📄 License

GPL v2.0 or later

---

**Made with ❤️ by Carlos Indriago**
