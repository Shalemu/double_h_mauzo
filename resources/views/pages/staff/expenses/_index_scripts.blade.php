<script>
document.addEventListener('DOMContentLoaded', function() {

    // 🔎 Search
    const searchInput = document.getElementById('table-search');
    const rows = document.querySelectorAll('#expenses-table tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // 📅 View expenses by date (AJAX)
    document.querySelectorAll('.view-date').forEach(link => {
        link.addEventListener('click', function() {
            const url = this.dataset.url;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('expense-details').innerHTML = html;
                })
                .catch(() => alert('Failed to load expenses.'));
        });
    });
});
</script>
