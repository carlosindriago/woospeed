/**
 * WooSpeed Analytics Generator - Modular Class
 *
 * Handles batch order generation with ES6 modules, private fields,
 * and encapsulated state management.
 *
 * @package WooSpeed_Analytics
 * @since 3.0.0
 */

class WooSpeedGenerator {
    // ========================================
    // Private Fields
    // ========================================

    #btn = null;
    #progressContainer = null;
    #progressBar = null;
    #processedSpan = null;
    #totalSpan = null;

    // Configuration
    #TOTAL_ORDERS = 5000;
    #BATCH_SIZE = 500;
    #processed = 0;

    // ========================================
    // Constructor
    // ========================================

    constructor() {
        this.#init();
    }

    // ========================================
    // Initialization
    // ========================================

    #init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.#setup());
        } else {
            this.#setup();
        }
    }

    #setup() {
        // Get DOM elements
        this.#btn = document.getElementById('btn-start-batch');

        if (!this.#btn) {
            console.warn('[WooSpeed] Generator button not found');
            return;
        }

        this.#progressContainer = document.getElementById('seed-progress-container');
        this.#progressBar = document.getElementById('seed-progress');
        this.#processedSpan = document.getElementById('processed-count');
        this.#totalSpan = document.getElementById('total-count');

        // Attach event listener
        this.#btn.addEventListener('click', () => this.#handleButtonClick());

        console.log('[WooSpeed] Generator initialized');
    }

    // ========================================
    // Event Handlers
    // ========================================

    async #handleButtonClick() {
        // Confirm before starting
        const confirmed = confirm(woospeed_vars.i18n.confirm_batch);

        if (!confirmed) {
            return;
        }

        // Reset state
        this.#processed = 0;

        // Update UI
        this.#btn.disabled = true;
        this.#progressContainer.style.display = 'block';
        this.#totalSpan.innerText = this.#TOTAL_ORDERS;
        this.#progressBar.value = 0;

        // Start processing
        await this.#processBatch();
    }

    async #processBatch() {
        // Check if complete
        if (this.#processed >= this.#TOTAL_ORDERS) {
            this.#onComplete();
            return;
        }

        try {
            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'woospeed_seed_batch');
            formData.append('batch_size', this.#BATCH_SIZE);
            formData.append('security', woospeed_vars.nonce);

            // Send request
            const response = await fetch(ajaxurl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.data?.message || 'Unknown error');
            }

            // Update progress
            this.#processed += this.#BATCH_SIZE;
            const percent = Math.min(100, (this.#processed / this.#TOTAL_ORDERS) * 100);

            this.#progressBar.value = percent;
            this.#processedSpan.innerText = Math.min(this.#processed, this.#TOTAL_ORDERS);

            // Continue processing
            await this.#processBatch();

        } catch (error) {
            console.error('[WooSpeed] Batch processing error:', error);
            this.#onError(error);
        }
    }

    // ========================================
    // Completion & Error Handlers
    // ========================================

    #onComplete() {
        alert(woospeed_vars.i18n.complete_batch);

        // Reload page to show new data
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    #onError(error) {
        // Show error alert
        const errorMsg = woospeed_vars.i18n.error_process + (error.message || '');
        alert(errorMsg);

        // Re-enable button
        this.#btn.disabled = false;

        console.error('[WooSpeed] Error:', error);
    }
}

// ========================================
// Initialize
// ========================================
new WooSpeedGenerator();
