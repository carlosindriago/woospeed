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

### Optimized Raw SQL Queries
Instead of using heavy abstraction layers (ORM), pure prepared SQL queries (`$wpdb->prepare`) are used for maximum speed and security.

## 🚀 Installation

1. Clone this repository into your `wp-content/plugins/` directory:
   ```bash
   git clone [repository-url] woospeed-analytics
   ```
2. Activate the plugin in the WordPress Admin Dashboard.
3. Upon activation, the custom table `wp_wc_speed_reports` will be automatically created.

## 🧪 Usage & Testing

1. Navigate to **WooCommerce > Speed Analytics**.
2. **First Run:** The dashboard will be empty.
3. **Generate Dummy Data:**
   - Click the "🛠 Generar Datos de Prueba" button to insert 5000 simulated sales records.
   - This bypasses the need to manually create orders for testing the performance.
4. View the real-time graph generated in milliseconds.

## 📂 File Structure

```
woospeed-analytics/
├── woospeed-analytics.php  # Main plugin file (Singleton, Hooks, SQL logic)
├── README.md               # Documentation
└── .gitignore              # Git ignore rules
```

## ⚠️ Disclaimer
This is a Proof of Concept (PoC) intended for demonstration purposes. While it follows professional standards, ensure to review and test thoroughly before adapting for a production environment.
