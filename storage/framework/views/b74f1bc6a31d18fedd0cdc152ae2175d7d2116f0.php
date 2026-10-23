<script>
document.addEventListener('DOMContentLoaded', function() {

    // SEARCH
    const searchInput = document.getElementById('table-search');
    const rows = document.querySelectorAll('#sales-table tbody tr');
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // AJAX: View sales by date
    document.querySelectorAll('.view-date').forEach(link => {
        link.addEventListener('click', function() {
            const url = this.dataset.url;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    // Hide main table
                    document.getElementById('sales-table-container').style.display = 'none';

                    // Show details container with a bordered card
                    const detailsDiv = document.getElementById('sales-details');
                    detailsDiv.innerHTML = `
                        <div class="card border shadow-sm">
                            <div class="card-body">
                                ${html}
                            </div>
                        </div>
                    `;
                    detailsDiv.style.display = 'block';

                    // Re-attach sale-type filter
                    attachSaleTypeFilter();
                })
                .catch(() => alert('Failed to load sales details.'));
        });
    });

    // Checkout button (optional)
    document.querySelectorAll('.checkout-btn').forEach(button => {
        button.addEventListener('click', function() {
            alert('Checkout logic here');
        });
    });

    // Attach filter for sale-type radio buttons
    function attachSaleTypeFilter() {
        const radios = document.querySelectorAll('input[name="sale-type"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedType = this.value;
                const table = document.getElementById('sales-detail-table');
                if (!table) return;

                const tableRows = table.querySelectorAll('tbody tr[data-sale-type]');
                let anyVisible = false;

                tableRows.forEach(row => {
                    const rowType = row.dataset.saleType || 'retail';
                    if (selectedType === 'both' || rowType === selectedType) {
                        row.style.display = '';
                        anyVisible = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show or hide "No sales" row
                const noSalesRow = document.getElementById('no-sales-row');
                if (noSalesRow) noSalesRow.style.display = anyVisible ? 'none' : '';
            });
        });

        // Trigger default filter on load
        const checkedRadio = document.querySelector('input[name="sale-type"]:checked');
        if (checkedRadio) checkedRadio.dispatchEvent(new Event('change'));
    }
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\staff\sales\_index_scripts.blade.php ENDPATH**/ ?>