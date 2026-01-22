# WooSpeed Analytics 🚀

**High-Performance Analytics Engine for WooCommerce**

WooSpeed Analytics is a professional WordPress plugin designed to solve the critical performance bottleneck in high-traffic WooCommerce stores: slow report generation caused by the native EAV (Entity-Attribute-Value) architecture of `wp_postmeta`.

By implementing a **simplified CQRS (Command Query Responsibility Segregation)** pattern with custom indexed Flat Tables and Raw SQL queries, WooSpeed delivers **sub-50ms dashboard loads** even with millions of orders—compared to 8-15 seconds with native WooCommerce reporting.

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| **⚡ Lightning Fast** | 0.01s query times via Flat Table architecture |
| **📊 Real-Time Dashboard** | KPIs, charts, and leaderboards with auto-refresh |
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

## 📊 Dashboard 2.0

- **4 KPI Cards**: Revenue, Orders, Avg Order Value, Max Order
- **Dynamic Date Filters**: 7 days, 30 days, Quarter, Full Year
- **Sales Trend Chart**: Interactive line chart with Chart.js
- **Top Products Leaderboard**: Real-time ranking by quantity sold
- **Auto-Refresh**: 10-second polling for live updates

---

## 🔒 Security Features

| Protection | Implementation |
|------------|----------------|
| **SQL Injection** | 100% `$wpdb->prepare()` coverage |
| **CSRF (Ajax)** | `check_ajax_referer()` on all endpoints |
| **CSRF (GET)** | `wp_verify_nonce()` + `wp_nonce_url()` |
| **Authorization** | `manage_woocommerce` / `manage_options` checks |
| **Direct Access** | `ABSPATH` and `WP_UNINSTALL_PLUGIN` guards |

**Security Score: 98%** (49/50) - See [Security Audit](./SECURITY.md)

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

---

## 📖 Usage

### Dashboard
Navigate to **Speed Analytics → Dashboard** to view:
- Real-time KPIs with colored indicator cards
- Sales trend chart with selectable date ranges
- Top 5 products by quantity sold

### Stress Test Generator
Navigate to **Speed Analytics → Data Generator** to:
1. **Generate Products**: Create 20 demo products
2. **Mass Load**: Generate 5,000 orders (batched AJAX)
3. **Quick Test**: Generate 50 orders
4. **Cleanup**: Remove all test data

---

## 📂 File Structure

```
woospeed-analytics/
├── assets/
│   ├── css/admin-style.css      # Dashboard styles
│   └── js/
│       ├── admin-dashboard.js   # Chart & KPI logic
│       └── admin-generator.js   # Batch seeding
├── includes/
│   ├── class-ws-repository.php  # Database layer
│   ├── class-ws-seeder.php      # Data generation
│   └── class-ws-api.php         # AJAX endpoints
├── admin/
│   ├── class-ws-admin.php       # Controller
│   └── partials/                # View templates
├── languages/                   # i18n files
├── uninstall.php                # Clean removal
└── woospeed-analytics.php       # Bootstrapper
```

---

## ⚡ Performance Benchmarks

| Metric | Native WooCommerce | WooSpeed Analytics |
|--------|--------------------|--------------------|
| KPI Query (5k orders) | 8-15 seconds | **< 50ms** |
| Chart Data | 5-10 seconds | **< 30ms** |
| Top Products | 3-8 seconds | **< 20ms** |
| Write (Order Sync) | O(n) | **O(1)** |

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
