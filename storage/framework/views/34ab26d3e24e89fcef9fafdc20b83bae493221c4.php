<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('staffSearch');
        if(searchInput){
            searchInput.addEventListener('keyup', function () {
                const filter = this.value.toUpperCase();
                document.querySelectorAll('.staff-table tbody tr').forEach(row => {
                    if(row.classList.contains('table-success')) return;
                    const nameCell = row.querySelector('td:first-child');
                    row.style.display = nameCell && nameCell.textContent.toUpperCase().includes(filter) ? '' : 'none';
                });
            });
        }
    });
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/admin/_staff_scripts.blade.php ENDPATH**/ ?>