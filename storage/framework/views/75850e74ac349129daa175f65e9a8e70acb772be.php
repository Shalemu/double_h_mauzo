<script>
document.addEventListener('DOMContentLoaded', function() {
    // Table search
    const searchInput = document.getElementById('expenses-table-search');
    const table = document.getElementById('expenses-table');
    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // View expenses by date
    document.querySelectorAll('.view-date').forEach(link => {
        link.addEventListener('click', function() {
            const url = this.dataset.url;
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('expense-details').innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to load expenses for this date.');
                });
        });
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\expenses\_index_scripts.blade.php ENDPATH**/ ?>