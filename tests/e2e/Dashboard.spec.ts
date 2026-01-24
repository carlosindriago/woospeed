import { test, expect } from '@playwright/test';

/**
 * E2E Tests for WooSpeed Analytics Dashboard
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

test.describe('WooSpeed Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // Login to WordPress admin
    await page.goto('/wp-admin');
    await page.fill('input[name="log"]', 'admin');
    await page.fill('input[name="pwd"]', 'password');
    await page.click('input[type="submit"]');

    // Wait for dashboard
    await page.waitForURL(/wp-admin/);
  });

  test('should display dashboard page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Check page title
    await expect(page.locator('h1')).toContainText('Performance Overview');

    // Check KPI cards exist
    await expect(page.locator('.woospeed-kpi-grid')).toBeVisible();
    await expect(page.locator('#kpi-revenue')).toBeVisible();
    await expect(page.locator('#kpi-orders')).toBeVisible();
    await expect(page.locator('#kpi-aov')).toBeVisible();
    await expect(page.locator('#kpi-max')).toBeVisible();
  });

  test('should display charts', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Check sales trend chart
    await expect(page.locator('#speedChart')).toBeVisible();

    // Check weekday chart
    await expect(page.locator('#weekdayChart')).toBeVisible();
  });

  test('should display leaderboards', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Check top products leaderboard
    await expect(page.locator('#leaderboard-container')).toBeVisible();

    // Check bottom products
    await expect(page.locator('#bottom-products-container')).toBeVisible();

    // Check top categories
    await expect(page.locator('#categories-container')).toBeVisible();
  });

  test('should open date picker on click', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Click date trigger
    await page.click('#woospeed-date-trigger');

    // Dropdown should be visible
    await expect(page.locator('#woospeed-date-dropdown')).toBeVisible();
    await expect(page.locator('#woospeed-date-dropdown')).toHaveClass(/open/);
  });

  test('should switch between presets and custom tabs', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Open date picker
    await page.click('#woospeed-date-trigger');

    // Click custom tab
    await page.click('button[data-tab="custom"]');

    // Custom panel should be visible
    await expect(page.locator('#woospeed-panel-custom')).toBeVisible();

    // Click presets tab
    await page.click('button[data-tab="presets"]');

    // Presets panel should be visible
    await expect(page.locator('#woospeed-panel-presets')).toBeVisible();
  });

  test('should update date range when preset is selected', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Open date picker
    await page.click('#woospeed-date-trigger');

    // Click "Today" preset
    await page.click('button[data-preset="today"]');

    // Close dropdown
    await page.click('#woospeed-date-update');

    // Label should be updated
    await expect(page.locator('#woospeed-date-label')).toContainText('Today');
  });

  test('should close dropdown when clicking outside', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Open date picker
    await page.click('#woospeed-date-trigger');

    // Click outside
    await page.click('.woospeed-header');

    // Dropdown should be closed
    await expect(page.locator('#woospeed-date-dropdown')).not.toHaveClass(/open/);
  });

  test('should display best and worst day cards', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-dashboard');

    // Check extremes grid exists
    await expect(page.locator('.woospeed-extremes-grid')).toBeVisible();

    // Check best day card
    await expect(page.locator('.woospeed-best-day')).toBeVisible();
    await expect(page.locator('#kpi-best-day')).toBeVisible();
    await expect(page.locator('#kpi-best-total')).toBeVisible();

    // Check worst day card
    await expect(page.locator('.woospeed-worst-day')).toBeVisible();
    await expect(page.locator('#kpi-worst-day')).toBeVisible();
    await expect(page.locator('#kpi-worst-total')).toBeVisible();
  });
});

test.describe('WooSpeed Settings', () => {
  test.beforeEach(async ({ page }) => {
    // Login
    await page.goto('/wp-admin');
    await page.fill('input[name="log"]', 'admin');
    await page.fill('input[name="pwd"]', 'password');
    await page.click('input[type="submit"]');
    await page.waitForURL(/wp-admin/);
  });

  test('should display settings page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-settings');

    // Check page title
    await expect(page.locator('h1')).toContainText('WooSpeed Analytics Settings');

    // Check sections
    await expect(page.locator('text=General Settings')).toBeVisible();
    await expect(page.locator('text=Dashboard Widgets')).toBeVisible();
    await expect(page.locator('text=Appearance')).toBeVisible();
    await expect(page.locator('text=Data Management')).toBeVisible();
  });

  test('should save settings', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-settings');

    // Change default date range
    await page.selectOption('#default_date_range', 'last_week');

    // Save settings
    await page.click('input[name="woospeed_save_settings"]');

    // Check for success notice
    await expect(page.locator('.notice-success')).toContainText('saved successfully');
  });
});

test.describe('WooSpeed Generator', () => {
  test.beforeEach(async ({ page }) => {
    // Login
    await page.goto('/wp-admin');
    await page.fill('input[name="log"]', 'admin');
    await page.fill('input[name="pwd"]', 'password');
    await page.click('input[type="submit"]');
    await page.waitForURL(/wp-admin/);
  });

  test('should display generator page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-generator');

    // Check page title
    await expect(page.locator('h1')).toContainText('Stress-Test Data Generator');

    // Check generator cards
    await expect(page.locator('text=First: Dummy Products')).toBeVisible();
    await expect(page.locator('text=Second: Mass Load (5k)')).toBeVisible();
    await expect(page.locator('text=Quick Test (50)')).toBeVisible();
  });

  test('should generate products', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=woospeed-generator');

    // Click generate products button (with confirmation)
    page.on('dialog', dialog => dialog.accept());

    await page.click('text=Generate Products (Step 1)');

    // Wait for navigation/redirect
    await page.waitForURL(/seeded=true/);

    // Check for success notice
    await expect(page.locator('.notice-success')).toContainText('Operation Complete');
  });
});
