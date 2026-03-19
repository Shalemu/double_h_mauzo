<!-- resources/views/dashboard/sales/detail.blade.php -->

<div class="container-fluid mt-4 main-content">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Daily Item Sales Detail for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                ({{ $shop->name ?? 'Double H Cosmetics Shop' }})
            </h5>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">&larr; Back</a>
        </div>

        <div class="card-body">

            <!-- SALE TYPE FILTER -->
            <div class="d-flex gap-2 mb-3">
                <input type="radio" class="btn-check" name="sale-type" id="retail" value="retail" checked>
                <label class="btn btn-outline-primary" for="retail">Retail</label>

                <input type="radio" class="btn-check" name="sale-type" id="wholesale" value="wholesale">
                <label class="btn btn-outline-success" for="wholesale">Wholesale</label>

                <input type="radio" class="btn-check" name="sale-type" id="both" value="both">
                <label class="btn btn-outline-dark" for="both">Both</label>
            </div>

            <!-- EXPORT + SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between flex-wrap align-items-center">

                    <!-- Export -->
                    <div class="btn-group mb-2">
                        <a href="{{ route('sales.export.excel', [$shop->id, $date]) }}"
                           class="btn btn-success btn-sm"
                           id="export-excel"
                           data-base-url="{{ route('sales.export.excel', [$shop->id, $date]) }}">
                            Export Excel
                        </a>

                        <a href="{{ route('sales.export.pdf', [$shop->id, $date]) }}"
                           class="btn btn-danger btn-sm"
                           id="export-pdf"
                           data-base-url="{{ route('sales.export.pdf', [$shop->id, $date]) }}">
                            Export PDF
                        </a>
                    </div>

                    <!-- Search -->
                    <div style="width: 250px;">
                        <input type="text" id="table-search"
                               class="form-control form-control-sm"
                               placeholder="Search sale...">
                    </div>

                </div>
            </div>

            <!-- SALES TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="sales-table">
                    <thead class="table-primary align-middle">
                        <tr>
                            <th>S/N</th>
                            <th>Item</th>
                            <th>Quantity Sold</th>
                            <th>Total (TZS)</th>
                            <th>Sold by</th>
                            <th>Sale Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sales-tbody">
                        @forelse($itemRows as $index => $row)
                        <tr data-sale-type="{{ strtolower(trim($row['sale_type'] ?? 'retail')) }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start">{{ $row['product'] }}</td>
                            <td>{{ $row['quantity'] }}</td>
                            <td class="fw-bold">{{ number_format($row['revenue'], 2) }}</td>
                            <td class="text-start">{{ $row['staff'] }}</td>
                            <td>{{ ucfirst($row['sale_type'] ?? 'Retail') }}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-receipt"
                                        data-product="{{ $row['product'] }}"
                                        data-staff="{{ $row['staff'] }}">
                                    View
                                </button>

                                <button class="btn btn-sm btn-warning return-sale-btn"
                                        data-sale-id="{{ $row['sale_id'] ?? '' }}"
                                        data-product-id="{{ $row['product_id'] ?? '' }}"
                                        data-product-name="{{ $row['product'] }}"
                                        data-quantity="{{ $row['quantity'] }}"
                                        data-price="{{ $row['revenue'] / $row['quantity'] ?? 0 }}"
                                        data-sale-type="{{ strtolower($row['sale_type'] ?? 'retail') }}">
                                    Return
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="no-sales-row">
                            <td colspan="7" class="text-center text-muted">
                                No items sold on this date.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- SALE RETURN MODAL -->
<div class="modal fade" id="returnSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="returnSaleForm" method="POST" action="{{ route('sales-returns.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Return Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="sale_id" id="returnSaleId">
                    <input type="hidden" name="product_id" id="returnProductId">
                    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                    <input type="hidden" name="sale_type" id="returnSaleType">

                    <div class="mb-3">
                        <label>Product</label>
                        <input type="text" id="returnProductName" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label>Quantity to Return</label>
                        <input type="number" name="quantity" id="returnQuantity" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label>Amount</label>
                        <input type="number" name="amount" id="returnAmount" class="form-control" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" placeholder="Optional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Return</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JS FILTER + RETURN -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // FILTER FUNCTION
    function filterTable() {
        const searchInput = document.getElementById('table-search');
        const selectedType = document.querySelector('input[name="sale-type"]:checked').value.toLowerCase();
        const tbody = document.getElementById('sales-tbody');
        const noSalesRow = document.getElementById('no-sales-row');

        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr[data-sale-type]'));
        const query = searchInput.value.toLowerCase();
        let visible = 0;

        rows.forEach(row => {
            const rowType = (row.dataset.saleType || 'retail').toLowerCase().trim();
            const text = Array.from(row.cells).map(td => td.textContent.toLowerCase()).join(' ');
            const matchType = selectedType === 'both' || rowType === selectedType;
            const matchSearch = text.includes(query);

            if (matchType && matchSearch) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        if (noSalesRow) noSalesRow.style.display = (visible === 0 ? '' : 'none');

        // Update export links
        const excelBtn = document.getElementById('export-excel');
        const pdfBtn = document.getElementById('export-pdf');
        if (excelBtn) excelBtn.href = excelBtn.dataset.baseUrl + '?type=' + selectedType;
        if (pdfBtn) pdfBtn.href = pdfBtn.dataset.baseUrl + '?type=' + selectedType;
    }

    // FILTER EVENTS
    const searchInput = document.getElementById('table-search');
    searchInput?.addEventListener('input', filterTable);
    document.querySelectorAll('input[name="sale-type"]').forEach(radio => {
        radio.addEventListener('change', filterTable);
    });

    filterTable(); // initial filter

    // --- EVENT DELEGATION FOR RETURN BUTTON ---
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.return-sale-btn');
        if (!btn) return;

        const saleId = btn.dataset.saleId;
        const productId = btn.dataset.productId;
        const productName = btn.dataset.productName;
        const quantity = parseInt(btn.dataset.quantity);
        const price = parseFloat(btn.dataset.price);
        const saleType = btn.dataset.saleType;

        document.getElementById('returnSaleId').value = saleId;
        document.getElementById('returnProductId').value = productId;
        document.getElementById('returnProductName').value = productName;
        document.getElementById('returnQuantity').max = quantity;
        document.getElementById('returnQuantity').value = 1;
        document.getElementById('returnAmount').value = price;
        document.getElementById('returnSaleType').value = saleType;

        const modal = new bootstrap.Modal(document.getElementById('returnSaleModal'));
        modal.show();
    });

    // --- AJAX DATE CLICK ---
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.view-date');
        if (!link) return;

        const url = link.dataset.url;
        fetch(url)
            .then(res => res.text())
            .then(html => {
                document.getElementById('sale-section').innerHTML = html;

                // After loading new content, re-apply filter
                filterTable();
            });
    });

});
</script>