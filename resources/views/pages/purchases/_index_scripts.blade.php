<script>
document.addEventListener('DOMContentLoaded', function () {

    // SEARCH
    document.getElementById('purchases-table-search').addEventListener('input', function () {
        let q = this.value.toLowerCase();
        document.querySelectorAll('#purchases-table tbody tr').forEach(row => {
            row.style.display = row.cells[1].innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // VIEW PURCHASE DETAILS
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('view-date')) {
            e.preventDefault();
            fetch(e.target.dataset.url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('purchase-details').innerHTML = html;
                });
        }
    });

    // New Purchase Order / New Item Purchase are their own pages now
    // (links in the header), not forms loaded into this one.
});
</script>