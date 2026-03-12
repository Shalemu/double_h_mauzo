<div class="card border shadow-sm mb-3">
    <div class="card-body">
        <!-- Header -->
        <h5 class="mb-3">Sales for {{ $date }}</h5>

        <!-- Sale Type Buttons -->
        <div class="d-flex gap-2 sale-type-group mb-3">
            <input type="radio" class="btn-check" name="sale-type" id="retail" value="retail" checked>
            <label class="btn btn-outline-primary flex-fill py-2" for="retail">
                <i class="fa fa-shopping-basket me-1"></i> Retail
            </label>

            <input type="radio" class="btn-check" name="sale-type" id="wholesale" value="wholesale">
            <label class="btn btn-outline-success flex-fill py-2" for="wholesale">
                <i class="fa fa-cubes me-1"></i> Wholesale
            </label>

            <input type="radio" class="btn-check" name="sale-type" id="both" value="both">
            <label class="btn btn-outline-dark flex-fill py-2" for="both">
                <i class="fa fa-exchange me-1"></i> Both
            </label>
        </div>

        
        <!-- Export Buttons -->
        <div class="d-flex justify-content-end mb-2 gap-2">
            <a href="{{ route('staff.sales.exportExcel', ['shop' => $shop->id, 'date' => $date, 'type' => 'retail']) }}" 
            class="btn btn-sm btn-success" id="export-excel">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('staff.sales.exportPdf', ['shop' => $shop->id, 'date' => $date, 'type' => 'retail']) }}" 
            class="btn btn-sm btn-danger">
                <i class="fa fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-striped" id="sales-detail-table">
                <thead class="table-light">
                    <tr>
                        <th>SN</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total (TZS)</th>
                        <th>Sale Type</th>
                    </tr>
                </thead>
                <tbody id="sales-tbody">
                    @forelse ($itemRows as $item)
                        <tr data-sale-type="{{ $item['sale_type'] ?? 'retail' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['product'] ?? 'Unknown' }}</td>
                            <td>{{ $item['quantity'] ?? 0 }}</td>
                            <td>{{ number_format($item['revenue'] ?? 0, 2) }}</td>
                            <td>{{ ucfirst($item['sale_type'] ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr id="no-sales-row">
                            <td colspan="5" class="text-center text-muted">No sales for this date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Sales pagination" class="mt-2">
            <ul class="pagination justify-content-center" id="sales-pagination"></ul>
        </nav>

        <!-- Back Button -->
        <button class="btn btn-secondary btn-sm mt-2" 
                onclick="document.getElementById('sales-details').style.display='none'; document.getElementById('sales-table-container').style.display='block';">
            ← Back to Sales
        </button>
    </div>
</div>

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
</script>