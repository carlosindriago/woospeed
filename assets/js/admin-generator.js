document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btn-start-batch');
    if (!btn) return;

    const progressContainer = document.getElementById('seed-progress-container');
    const progressBar = document.getElementById('seed-progress');
    const processedSpan = document.getElementById('processed-count');
    const totalSpan = document.getElementById('total-count');

    // Constants
    const TOTAL_ORDERS = 5000;
    const BATCH_SIZE = 500;
    let processed = 0;

    btn.addEventListener('click', function () {
        if (!confirm('Esto generará 5,000 pedidos reales. ¿Continuar?')) return;

        btn.disabled = true;
        progressContainer.style.display = 'block';
        processed = 0;
        totalSpan.innerText = TOTAL_ORDERS;
        progressBar.value = 0;

        processBatch();
    });

    function processBatch() {
        if (processed >= TOTAL_ORDERS) {
            alert('✅ ¡Proceso Terminado! 5,000 Pedidos Generados.');
            window.location.reload();
            return;
        }

        const data = new FormData();
        data.append('action', 'woospeed_seed_batch');
        data.append('batch_size', BATCH_SIZE);
        // Use localized nonce
        if (typeof woospeed_vars !== 'undefined') {
            data.append('security', woospeed_vars.nonce);
        } else {
            console.error('WooSpeed Security Nonce not found');
            return;
        }

        fetch(ajaxurl, {
            method: 'POST',
            body: data
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    processed += BATCH_SIZE;
                    const percent = Math.min(100, (processed / TOTAL_ORDERS) * 100);
                    progressBar.value = percent;
                    processedSpan.innerText = Math.min(processed, TOTAL_ORDERS);

                    // Recursion
                    processBatch();
                } else {
                    alert('Error en el proceso: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert('Error de red. Intenta de nuevo.');
                btn.disabled = false;
            });
    }
});
