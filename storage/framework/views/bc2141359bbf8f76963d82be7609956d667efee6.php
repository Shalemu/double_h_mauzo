<script>
document.getElementById('supplierSelect').addEventListener('change', function () {
    let supplierId = this.value;
    let options = document.querySelectorAll('#purchaseSelect option');

    options.forEach(option => {
        if (!option.value) return; // skip default

        if (!supplierId || option.dataset.supplier === supplierId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });

    // reset selected value
    document.getElementById('purchaseSelect').value = '';
});

// credit history
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-history').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(this.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    const modal = document.getElementById('historyModal');
                    modal.querySelector('.modal-body').innerHTML = html;
                    new bootstrap.Modal(modal).show();
                });
        });
    });
});

</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\credit\_index_scripts.blade.php ENDPATH**/ ?>