<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="sale-type"]');
    const tbody = document.getElementById('sales-tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-sale-type]'));
    const noSalesRow = document.getElementById('no-sales-row');
    const pagination = document.getElementById('sales-pagination');
    const rowsPerPage = 5;
    let currentPage = 1;
    let filteredRows = [...rows];

    // Export buttons
    const excelBtn = document.getElementById('export-excel');
    const pdfBtn = document.getElementById('export-pdf');

    function renderTable() {
        rows.forEach(r => r.style.display = 'none');
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const visibleRows = filteredRows.slice(start, end);

        visibleRows.forEach(r => r.style.display = '');
        if (visibleRows.length === 0 && noSalesRow) noSalesRow.style.display = '';
        else if (noSalesRow) noSalesRow.style.display = 'none';

        renderPagination();
    }

    function renderPagination() {
        pagination.innerHTML = '';
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = 'page-item' + (i === currentPage ? ' active' : '');
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = i;
            a.addEventListener('click', e => {
                e.preventDefault();
                currentPage = i;
                renderTable();
            });
            li.appendChild(a);
            pagination.appendChild(li);
        }
    }

    function filterRows() {
        const selectedType = document.querySelector('input[name="sale-type"]:checked').value;

        // Filter table rows
        filteredRows = rows.filter(r => {
            const rowType = r.dataset.saleType || 'retail';
            return selectedType === 'both' || rowType === selectedType;
        });

        // Update export links dynamically
        if (excelBtn) {
            const baseExcel = excelBtn.dataset.baseUrl; // set base URL in HTML
            excelBtn.href = baseExcel + '?type=' + selectedType;
        }
        if (pdfBtn) {
            const basePdf = pdfBtn.dataset.baseUrl; // set base URL in HTML
            pdfBtn.href = basePdf + '?type=' + selectedType;
        }

        currentPage = 1;
        renderTable();
    }

    radios.forEach(radio => radio.addEventListener('change', filterRows));

    // Initial render
    filterRows();
});
</script><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\staff\sales\_detail_scripts.blade.php ENDPATH**/ ?>