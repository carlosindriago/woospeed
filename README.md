# WooSpeed Analytics 🚀

**WooSpeed Analytics** is a Proof of Concept (PoC) plugin for WooCommerce designed to solve the critical performance bottleneck in high-traffic stores: slow report generation due to the native EAV (Entity-Attribute-Value) structure of `wp_postmeta`.

This plugin implements a **simplified CQRS (Command Query Responsibility Segregation)** architecture, moving transactional data to a custom Flat Indexed Table. This allows for generating historical reports in real-time with **O(1)** computational complexity instead of O(n), reducing load times from >8s to <0.05s even with millions of records.

## 🛠️ Tech Stack & Architecture

- **Language:** PHP 8.1 (Strict Typing Standards)
- **Database:** MySQL 8.0 (Custom Storage Engine)
- **Frontend:** Chart.js (Asynchronous Rendering)
- **Design Patterns:** Singleton, Observer (Hooks), CQRS

## 💡 Technical Solution

### Bypass of `wp_postmeta`
Native WooCommerce performs multiple JOINs on the `wp_postmeta` table (key-value structure), which is inefficient for large-scale aggregations (SUM, COUNT).

**Solution:** A dedicated table `wp_wc_speed_reports` was implemented with optimized indexes on `report_date` and `order_id`.

### Asynchronous Synchronization (Event-Driven)
- Uses the `woocommerce_order_status_completed` hook to intercept the order at the moment of completion.
- Relevant data is denormalized and inserted into the flat table.

### 🔄 Smart Lifecycle Management
- **Automatic Cleanup:** Listens to `woocommerce_order_status_changed`.
- If an order is **cancelled, refunded, or failed**, it is immediately removed from the analytics table to prevent reporting errors (false positives).
- If reactivated, it re-syncs automatically.

### Optimized Raw SQL Queries
Instead of using heavy abstraction layers (ORM), pure prepared SQL queries (`$wpdb->prepare`) are used for maximum speed and security.

### ⚡ Performance Optimizations (v1.2.0)
- **Bulk Inserts:** Analytics seeding uses batched INSERTs (100 rows/query) reducing DB round-trips by 99%.
- **Email Suppression:** WooCommerce emails are disabled during dummy order generation to prevent spam.
- **Smart Tagging:** All dummy orders/products are tagged with `_woospeed_dummy` for reliable cleanup.

### 🎨 Dashboard 2.0 (v2.0.0)
- **Premium UI:** Modern CSS Grid layout with CSS variables and hover animations.
- **4 KPI Cards:** Total Revenue, Orders, Average Order Value, Max Order.
- **Dynamic Date Filter:** 7 days, 30 days, Quarter, or Full Year.
- **Top Products Leaderboard:** Ranks best-selling products from granular order items.
- **Real-Time Updates:** Auto-refreshes every 10 seconds via AJAX polling.
- **Granular Data Table:** `wp_wc_speed_order_items` stores individual product data for detailed analytics.

## 🚀 Installation

1. Clone this repository into your `wp-content/plugins/` directory:
   ```bash
   git clone https://github.com/carlosindriago/woospeed.git woospeed-analytics
   ```
2. Activate the plugin in the WordPress Admin Dashboard.
3. Upon activation, two custom tables are automatically created:
   - `wp_wc_speed_reports` - Order totals for KPIs and charts
   - `wp_wc_speed_order_items` - Individual product data for Top Products

## 🧪 Usage & Testing

1. Navigate to **Speed Analytics** in the main sidebar.
2. **Dashboard Tab (v2.0):**
   - **KPI Cards:** View Total Revenue, Orders, Avg Order Value, and Max Order at a glance.
   - **Date Filter:** Select 7 days, 30 days, Quarter, or Full Year from the dropdown.
   - **Sales Trend Chart:** Visualize daily revenue trends with auto-updating data.
   - **Top Products Leaderboard:** See your 5 best-selling products ranked by quantity.
   - **Real-Time:** Dashboard auto-refreshes every 10 seconds.
3. **Generator Tab (Stress Test):**
   - **Step 1: Products (Mandatory):** Generates 20 real WooCommerce products.
   - **Step 2: Massive Load (5k):** Uses **AJAX Batched Processing** (10 x 500) to generate 5,000 real orders.
   - **Realistic Simulation:** Orders are created with random dates (**last 90 days**).
4. **Cleanup:**
   - Use the **Danger Zone** to wipe all generated data (products, orders, and analytics).

## 🛡️ Security Features
- **Nonce Verification:** Every AJAX request is protected by a cryptographic Nonce (`woospeed_seed_nonce`) to prevent CSRF attacks.
- **Capability Checks:** Strict `current_user_can('manage_options')` validation on all sensitive endpoints.
- **Prepared Statements:** 100% protection against SQL Injection using `$wpdb->prepare`.

## 🛡️ Clean Uninstall Protocol
This plugin respects your database. Upon deletion:
- `uninstall.php` is triggered.
- Both custom tables are **completely dropped**:
  - `wp_wc_speed_reports`
  - `wp_wc_speed_order_items`
- No residual data is left behind.

## 📂 File Structure

```
woospeed-analytics/
├── woospeed-analytics.php  # Main Plugin Core (Singleton, CQRS, UI)
├── uninstall.php           # Clean Uninstall Protocol (Drop Table)
├── README.md               # Documentation
└── .gitignore              # Git ignore rules
```

## ⚠️ Disclaimer
This is a Proof of Concept (PoC) intended for demonstration purposes. While it follows professional standards, ensure to review and test thoroughly before adapting for a production environment.
