<script>
document.addEventListener('DOMContentLoaded', function () {
    const addForm = document.getElementById('addStoreForm');
    document.getElementById('toggleAddStore').addEventListener('click', function () {
        addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('storeSearch').addEventListener('keyup', function () {
        const filter = this.value.toUpperCase();
        document.querySelectorAll('.store-table tbody tr').forEach(tr => {
            if (tr.classList.contains('table-success')) return;
            const nameCell = tr.querySelector('td:first-child');
            tr.style.display = nameCell && nameCell.textContent.toUpperCase().includes(filter) ? '' : 'none';
        });
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\stores\_index_scripts.blade.php ENDPATH**/ ?>