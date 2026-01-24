<?php
/**
 * Migration View
 * 
 * Displays migration progress and controls.
 */

if (!defined('ABSPATH')) {
    exit;
}

$migration = get_option('woospeed_migration_status', []);
$status = $migration['status'] ?? 'not_needed';
$total = $migration['total_orders'] ?? 0;
$migrated = $migration['migrated_count'] ?? 0;
$errors = $migration['error_count'] ?? 0;
$percent = $total > 0 ? round(($migrated / $total) * 100) : 0;
?>

<div class="wrap woospeed-migration">
    <h1>
        <?php _e('🔄 Data Migration', 'woospeed-analytics'); ?>
    </h1>

    <?php if ($status === 'completed'): ?>
        <div class="woospeed-migration-success">
            <div class="woospeed-success-icon">✅</div>
            <h2>
                <?php _e('Migration Complete!', 'woospeed-analytics'); ?>
            </h2>
            <p>
                <?php printf(
                    __('Successfully migrated %s orders to WooSpeed Analytics.', 'woospeed-analytics'),
                    '<strong>' . number_format($migrated) . '</strong>'
                ); ?>
            </p>
            <?php if ($errors > 0): ?>
                <p class="woospeed-warning">
                    <?php printf(__('%s orders had errors and were skipped.', 'woospeed-analytics'), $errors); ?>
                </p>
            <?php endif; ?>
            <a href="<?php echo admin_url('admin.php?page=woospeed-dashboard'); ?>" class="button button-primary">
                <?php _e('Go to Dashboard', 'woospeed-analytics'); ?>
            </a>
        </div>

    <?php elseif ($status === 'not_needed'): ?>
        <div class="woospeed-migration-info">
            <div class="woospeed-info-icon">ℹ️</div>
            <h2>
                <?php _e('No Migration Needed', 'woospeed-analytics'); ?>
            </h2>
            <p>
                <?php _e('All orders are already synchronized with WooSpeed Analytics.', 'woospeed-analytics'); ?>
            </p>
            <a href="<?php echo admin_url('admin.php?page=woospeed-dashboard'); ?>" class="button">
                <?php _e('Go to Dashboard', 'woospeed-analytics'); ?>
            </a>
        </div>

    <?php else: ?>
        <!-- Migration Panel -->
        <div class="woospeed-migration-panel">
            <div class="woospeed-migration-header">
                <h2>
                    <?php _e('Order Synchronization Required', 'woospeed-analytics'); ?>
                </h2>
                <p>
                    <?php printf(
                        __('We found %s WooCommerce orders that need to be synchronized for accurate analytics.', 'woospeed-analytics'),
                        '<strong>' . number_format($total) . '</strong>'
                    ); ?>
                </p>
            </div>

            <!-- Progress Section -->
            <div class="woospeed-migration-progress" id="migration-progress">
                <div class="woospeed-progress-bar-container">
                    <div class="woospeed-progress-bar" id="progress-bar" style="width: <?php echo $percent; ?>%"></div>
                </div>
                <div class="woospeed-progress-stats">
                    <span id="progress-text">
                        <?php echo $percent; ?>%
                    </span>
                    <span id="progress-orders">
                        <?php echo number_format($migrated); ?> /
                        <?php echo number_format($total); ?>
                    </span>
                </div>
            </div>

            <!-- Status Messages -->
            <div class="woospeed-migration-status" id="migration-status">
                <div class="woospeed-status-message" id="status-message">
                    <?php if ($status === 'in_progress'): ?>
                        <?php _e('Migration in progress...', 'woospeed-analytics'); ?>
                    <?php else: ?>
                        <?php _e('Ready to start migration.', 'woospeed-analytics'); ?>
                    <?php endif; ?>
                </div>
                <div class="woospeed-status-details" id="status-details"></div>
            </div>

            <!-- Error Display -->
            <div class="woospeed-migration-errors" id="migration-errors" style="display: none;">
                <h4>
                    <?php _e('Errors', 'woospeed-analytics'); ?>
                </h4>
                <ul id="error-list"></ul>
            </div>

            <!-- Controls -->
            <div class="woospeed-migration-controls">
                <button type="button" id="btn-start-migration" class="button button-primary button-hero" <?php echo $status === 'in_progress' ? 'disabled' : ''; ?>>
                    <?php echo $status === 'in_progress' ? __('Migration in Progress...', 'woospeed-analytics') : __('Start Migration', 'woospeed-analytics'); ?>
                </button>
                <button type="button" id="btn-cancel-migration" class="button button-secondary" style="display: none;">
                    <?php _e('Pause', 'woospeed-analytics'); ?>
                </button>
            </div>

            <div class="woospeed-migration-info-box">
                <p><strong>
                        <?php _e('What happens during migration?', 'woospeed-analytics'); ?>
                    </strong></p>
                <ul>
                    <li>
                        <?php _e('Orders are processed in batches of 50 to avoid server overload.', 'woospeed-analytics'); ?>
                    </li>
                    <li>
                        <?php _e('Each order\'s totals and product items are synchronized.', 'woospeed-analytics'); ?>
                    </li>
                    <li>
                        <?php _e('You can pause and resume at any time.', 'woospeed-analytics'); ?>
                    </li>
                    <li>
                        <?php _e('The page will auto-refresh progress every few seconds.', 'woospeed-analytics'); ?>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .woospeed-migration {
        max-width: 800px;
        margin: 20px auto;
    }

    .ws-migration-panel {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .ws-migration-header h2 {
        margin-top: 0;
        color: #1d2327;
    }

    .ws-progress-bar-container {
        background: #e5e7eb;
        border-radius: 10px;
        height: 24px;
        overflow: hidden;
        margin: 20px 0 10px;
    }

    .ws-progress-bar {
        background: linear-gradient(90deg, #6366f1, #818cf8);
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .ws-progress-stats {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: #6b7280;
    }

    #progress-text {
        font-weight: 600;
        color: #6366f1;
        font-size: 18px;
    }

    .ws-migration-status {
        margin: 20px 0;
        padding: 15px;
        background: #f8fafc;
        border-radius: 6px;
    }

    .ws-status-message {
        font-weight: 500;
        color: #374151;
    }

    .ws-migration-errors {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 6px;
        padding: 15px;
        margin: 15px 0;
    }

    .ws-migration-errors h4 {
        margin: 0 0 10px;
        color: #dc2626;
    }

    .ws-migration-errors ul {
        margin: 0;
        padding-left: 20px;
        max-height: 150px;
        overflow-y: auto;
    }

    .ws-migration-controls {
        margin: 25px 0;
        text-align: center;
    }

    .ws-migration-controls .button-hero {
        font-size: 16px;
        padding: 12px 40px;
        height: auto;
    }

    .ws-migration-info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        padding: 15px 20px;
        margin-top: 20px;
    }

    .ws-migration-info-box ul {
        margin: 10px 0 0 20px;
        padding: 0;
    }

    .ws-migration-info-box li {
        margin-bottom: 5px;
        color: #475569;
    }

    .ws-migration-success,
    .ws-migration-info {
        text-align: center;
        padding: 40px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
    }

    .ws-success-icon,
    .ws-info-icon {
        font-size: 48px;
        margin-bottom: 20px;
    }

    .ws-warning {
        color: #b45309;
        background: #fffbeb;
        padding: 10px 15px;
        border-radius: 4px;
        display: inline-block;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const startBtn = document.getElementById('btn-start-migration');
        const cancelBtn = document.getElementById('btn-cancel-migration');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const progressOrders = document.getElementById('progress-orders');
        const statusMessage = document.getElementById('status-message');
        const statusDetails = document.getElementById('status-details');
        const errorsContainer = document.getElementById('migration-errors');
        const errorList = document.getElementById('error-list');

        let isRunning = false;
        let currentOffset = <?php echo $migrated; ?>;
        const batchSize = 50;
        const total = <?php echo $total; ?>;

        if (startBtn) {
            startBtn.addEventListener('click', startMigration);
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', pauseMigration);
        }

        function updateUI(migrated, errors) {
            const percent = total > 0 ? Math.round((migrated / total) * 100) : 0;
            if (progressBar) progressBar.style.width = percent + '%';
            if (progressText) progressText.textContent = percent + '%';
            if (progressOrders) progressOrders.textContent = migrated.toLocaleString() + ' / ' + total.toLocaleString();
        }

        function addError(message) {
            if (errorsContainer && errorList) {
                errorsContainer.style.display = 'block';
                const li = document.createElement('li');
                li.textContent = message;
                errorList.appendChild(li);
            }
        }

        function startMigration() {
            if (isRunning) return;
            isRunning = true;

            startBtn.disabled = true;
            startBtn.textContent = '<?php echo esc_js(__('Processing...', 'woospeed-analytics')); ?>';
    cancelBtn.style.display = 'inline-block';
    statusMessage.textContent = '<?php echo esc_js(__('Migration in progress...', 'woospeed-analytics')); ?>';

    processBatch();
    }

    function pauseMigration() {
        isRunning = false;
        startBtn.disabled = false;
        startBtn.textContent = '<?php echo esc_js(__('Resume Migration', 'woospeed-analytics')); ?>';
        cancelBtn.style.display = 'none';
        statusMessage.textContent = '<?php echo esc_js(__('Migration paused.', 'woospeed-analytics')); ?>';
    }

    function processBatch() {
        if (!isRunning) return;

        const formData = new FormData();
        formData.append('action', 'woospeed_migrate_batch');
        formData.append('security', '<?php echo wp_create_nonce('woospeed_migration_nonce'); ?>');
        formData.append('offset', currentOffset);
        formData.append('batch_size', batchSize);

        statusDetails.textContent = '<?php echo esc_js(__('Processing orders', 'woospeed-analytics')); ?> ' + (currentOffset + 1) + ' - ' + Math.min(currentOffset + batchSize, total) + '...';

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(response => {
                if (!response.success) {
                    addError(response.data || 'Unknown error');
                    pauseMigration();
                    return;
                }

                const data = response.data;
                currentOffset = data.migrated_count;
                updateUI(data.migrated_count, data.error_count);

                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(addError);
                }

                if (data.status === 'completed') {
                    isRunning = false;
                    statusMessage.textContent = '<?php echo esc_js(__('Migration complete!', 'woospeed-analytics')); ?>';
                    statusDetails.textContent = '';
                    startBtn.textContent = '<?php echo esc_js(__('✅ Done!', 'woospeed-analytics')); ?>';
                    cancelBtn.style.display = 'none';

                    // Reload page after 2 seconds to show success state
                    setTimeout(() => location.reload(), 2000);
                } else if (isRunning) {
                    // Small delay to not overwhelm server
                    setTimeout(processBatch, 200);
                }
            })
            .catch(err => {
                addError('Network error: ' + err.message);
                pauseMigration();
            });
    }

    // If already in progress, auto-resume
    <?php if ($status === 'in_progress'): ?>
            startMigration();
    <?php endif; ?>
});
</script>