# WooSpeed Analytics 🚀

**High-Performance Analytics Engine for WooCommerce**

WooSpeed Analytics is a professional WordPress plugin designed to solve the critical performance bottleneck in high-traffic WooCommerce stores: slow report generation caused by the native EAV (Entity-Attribute-Value) architecture of `wp_postmeta`.

By implementing a **simplified CQRS (Command Query Responsibility Segregation)** pattern with custom indexed Flat Tables and Raw SQL queries, WooSpeed delivers **sub-50ms dashboard loads** even with millions of orders—compared to 8-15 seconds with native WooCommerce reporting.

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| **⚡ Lightning Fast** | 0.01s query times via Flat Table architecture |
| **📊 Dashboard 3.0** | Advanced KPIs, charts, and leaderboards with auto-refresh |
| **📅 Advanced Date Picker** | WooCommerce Analytics-style presets (Today, Week, Month, Quarter, Year) |
| **⚙️ Settings Page** | Full customization of widgets and behavior |
| **🔄 Order Migration** | Batch import of existing WooCommerce orders |
| **🔒 Enterprise Security** | CSRF protection, prepared statements, capability checks |
| **🌍 Multilingual (i18n)** | English source + Spanish translation included |
| **🏗️ Modular Architecture** | SRP-based class structure for maintainability |
| **🧹 Clean Uninstall** | Complete data removal on plugin deletion |

---

## 🛠️ Technical Architecture

### Stack
- **PHP 8.1+** with strict typing standards
- **MySQL 8.0+** with indexed custom tables
- **Chart.js** for interactive visualizations
- **WordPress 6.x** native integration

### Design Patterns
- **CQRS**: Separate read (Flat Tables) and write (WooCommerce hooks) paths
- **Repository Pattern**: `WooSpeed_Repository` for all DB operations
- **Single Responsibility**: Dedicated classes for API, Seeder, Admin, Repository
- **Observer Pattern**: Event-driven sync via WooCommerce hooks

### Database Schema
```sql
wp_wc_speed_reports       -- Order aggregates (indexed: order_id, report_date)
wp_wc_speed_order_items   -- Product-level details (indexed: product_id, report_date)
```

---

## 📊 Dashboard 3.0 (NEW!)

### KPI Cards
| Card | Description |
|------|-------------|
| 💰 **Total Revenue** | Sum of all order totals |
| 📦 **Orders** | Count of orders in period |
| 📈 **Avg Order Value** | Revenue / Orders |
| 🏆 **Max Order** | Highest single order value |
| 🚀 **Best Sales Day** | Day with highest revenue |
| 📉 **Lowest Sales Day** | Day with lowest revenue |

### Charts
- **Sales Trend**: Interactive line chart showing daily revenue
- **Sales by Day of Week**: Bar chart showing Mon-Sun distribution

### Leaderboards
- **Top Products**: Best-selling products by quantity
- **Least Sold Products**: Lowest-performing products
- **Top Categories**: Categories ranked by revenue

### Date Range Picker
WooCommerce Analytics-style presets:
- Today, Yesterday
- Week to date, Last week
- Month to date, Last month
- Quarter to date, Last quarter
- Year to date, Last year
- Custom date range

---

## ⚙️ Settings Page (NEW!)

Navigate to **Speed Analytics → Settings** to customize:

### General Settings
- **Default Date Range**: Choose the default period when opening dashboard
- **Auto-Refresh Interval**: Set automatic refresh (10s to 5min, or disabled)

### Dashboard Widgets
Toggle visibility of each widget:
- KPI Cards
- Sales Trend Chart
- Sales by Day of Week Chart
- Top Products Leaderboard
- Least Sold Products
- Top Categories

### Appearance
- **Theme**: Auto (System) / Light / Dark

### Data Management
- Link to Migration Tool for syncing existing orders
- Database statistics (record counts)

### Developer Tools
- **Generate Dummy Data**: Create 10-1000 test orders
- **Clean Dummy Data**: Remove all test data

---

## 🔄 Order Migration System

On first activation, WooSpeed detects existing WooCommerce orders and offers to migrate them:

1. **Admin Notice**: Alert showing number of orders to migrate
2. **Migration Page**: Progress bar with batch processing
3. **Status Tracking**: Percentage complete, error handling, resumable

---

## 🔒 Security Features

| Protection | Implementation |
|------------|----------------|
| **SQL Injection** | 100% `$wpdb->prepare()` coverage |
| **CSRF (Ajax)** | `check_ajax_referer()` on all endpoints |
| **CSRF (GET)** | `wp_verify_nonce()` + `wp_nonce_url()` |
| **Authorization** | `manage_woocommerce` / `manage_options` checks |
| **Direct Access** | `ABSPATH` and `WP_UNINSTALL_PLUGIN` guards |

**Security Score: 98%** (49/50)

---

## 🌍 Internationalization

- **Source Language**: English
- **Included Translations**: Spanish (es_ES)
- **JavaScript Strings**: Fully localized via `wp_localize_script`
- **Translation-Ready**: `.pot` template included for new languages

---

## 🚀 Installation

1. Clone or download to `wp-content/plugins/`:
   ```bash
   git clone https://github.com/carlosindriago/woospeed.git woospeed-analytics
   ```

2. Activate the plugin in WordPress Admin

3. Custom tables are created automatically on activation:
   - `wp_wc_speed_reports`
   - `wp_wc_speed_order_items`

4. If you have existing orders, complete the migration when prompted

---

## 📖 Usage

### Dashboard
Navigate to **Speed Analytics → Dashboard** to view:
- Real-time KPIs with colored indicator cards
- Best and worst performing days
- Sales trend and weekday distribution charts
- Product and category leaderboards

### Settings
Navigate to **Speed Analytics → Settings** to:
- Configure default date range
- Toggle visible widgets
- Set auto-refresh interval
- Access developer tools

---

## 📂 File Structure

```
woospeed-analytics/
├── assets/
│   ├── css/admin-style.css      # Dashboard & Settings styles
│   └── js/
│       ├── admin-dashboard.js   # Charts, KPIs, Leaderboards
│       └── admin-generator.js   # Batch seeding
├── includes/
│   ├── class-ws-repository.php  # Database layer (10 query methods)
│   ├── class-ws-seeder.php      # Data generation
│   └── class-ws-api.php         # AJAX endpoints
├── admin/
│   ├── class-ws-admin.php       # Controller
│   └── partials/
│       ├── ws-dashboard-view.php   # Dashboard UI
│       ├── ws-settings-view.php    # Settings UI (NEW)
│       ├── ws-migration-view.php   # Migration UI
│       └── ws-generator-view.php   # Generator UI
├── languages/                   # i18n files
├── uninstall.php                # Clean removal
└── woospeed-analytics.php       # Bootstrapper (v3.0.0)
```

---

## ⚡ Performance Benchmarks

| Metric | Native WooCommerce | WooSpeed Analytics |
|--------|--------------------|--------------------
| KPI Query (5k orders) | 8-15 seconds | **< 50ms** |
| Chart Data | 5-10 seconds | **< 30ms** |
| Top Products | 3-8 seconds | **< 20ms** |
| Weekday Analysis | N/A | **< 25ms** |
| Category Ranking | 5-10 seconds | **< 30ms** |
| Write (Order Sync) | O(n) | **O(1)** |

---

## 📝 Changelog

### v3.0.0 (2026-01-22)
- **Dashboard 3.0**: Best/Worst Day cards, Weekday chart, Bottom Products, Top Categories
- **Settings Page**: Full plugin configuration UI
- **Developer Tools**: Moved dummy data generator to settings
- **UI Polish**: New leaderboard designs, empty states

### v2.2.0
- Order Migration System with progress bar
- Advanced Date Range Picker (WooCommerce Analytics style)

### v2.0.0
- Security Audit (98% score)
- Production-ready README

### v1.0.0
- Initial release with Flat Table architecture

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:
1. Fork the repository
2. Create a feature branch from `develop`
3. Follow WordPress Coding Standards
4. Submit a Pull Request with clear description

---

## 📄 License

This project is licensed under the GPL v2 or later.

---

## 👨‍💻 Author

**Carlos Indriago**  
Senior Software Engineer specializing in high-performance WooCommerce solutions.

---

*Built with ❤️ for the WooCommerce community*
