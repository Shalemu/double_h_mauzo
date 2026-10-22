<script>
(function () {
    const inputs = document.querySelectorAll('.price-input');
    const saveBtn = document.getElementById('saveBtn');
    const changeCount = document.getElementById('changeCount');

    function refreshRow(row) {
        const cost = parseFloat(row.querySelector('[data-field="purchase_price"]').value) || 0;
        const sell = parseFloat(row.querySelector('[data-field="selling_price"]').value) || 0;
        const cell = row.querySelector('.margin-cell');
        if (!sell) { cell.textContent = '—'; cell.className = 'margin-cell text-muted'; return; }
        const margin = ((sell - cost) / sell) * 100;
        cell.textContent = margin.toFixed(1) + '%';
        cell.className = 'margin-cell ' + (margin < 0 ? 'text-danger fw-semibold' : 'text-success');
    }

    function refresh() {
        let changed = 0;
        inputs.forEach(function (input) {
            const dirty = String(parseFloat(input.value)) !== String(parseFloat(input.dataset.original));
            // Only changed fields are submitted, so untouched rows are never rewritten.
            if (dirty) { input.name = input.dataset.name; changed++; } else { input.removeAttribute('name'); }
            input.classList.toggle('border-primary', dirty);
        });
        saveBtn.disabled = changed === 0;
        changeCount.textContent = changed ? changed + ' change(s)' : 'No changes';
    }

    inputs.forEach(function (input) {
        input.addEventListener('input', function () { refreshRow(input.closest('[data-row]')); refresh(); });
    });
    document.querySelectorAll('[data-row]').forEach(refreshRow);
    refresh();
})();
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\procurement\_price_manager_scripts.blade.php ENDPATH**/ ?>