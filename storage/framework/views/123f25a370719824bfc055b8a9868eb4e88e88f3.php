<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('sales-table-search');
    const table = document.getElementById('sales-table');
    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();

        rows.forEach(row => {
            const cellsText = Array.from(row.cells).map(td => td.textContent.toLowerCase()).join(' ');
            row.style.display = cellsText.includes(query) ? '' : 'none';
        });
    });
});

document.querySelectorAll('.view-date').forEach(link => {
    link.addEventListener('click', function() {
        const url = this.dataset.url;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                document.getElementById('sale-section').innerHTML = html;
                attachSaleTypeFilter();
            });
    });
});

// "View" button - the detail table is loaded via AJAX, so listen via delegation
document.addEventListener('click', function(e) {
    const viewBtn = e.target.closest('.view-receipt');
    if (!viewBtn) return;

    Swal.fire({
        title: viewBtn.dataset.product,
        html: `
            <div style="text-align:left">
                <p><strong>Sold by:</strong> ${viewBtn.dataset.staff}</p>
                <p><strong>Quantity Sold:</strong> ${viewBtn.dataset.quantity}</p>
                <p><strong>Sale Type:</strong> ${viewBtn.dataset.saleType}</p>
                <p><strong>Total:</strong> Tsh ${viewBtn.dataset.total}</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close'
    });
});


function attachSaleTypeFilter() {
        const radios = document.querySelectorAll('input[name="sale-type"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedType = this.value;
                const table = document.getElementById('sales-table');
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

</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\sales\_index_scripts.blade.php ENDPATH**/ ?>