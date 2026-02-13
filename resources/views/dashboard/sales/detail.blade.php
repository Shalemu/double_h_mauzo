
<!-- MAIN CONTENT -->
<div class="container-fluid mt-4 main-content">

    <!-- SALES REPORT CARD -->
    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">         
                Daily Item Sales Detail for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                ({{ $shop->name ?? 'Double H Cosmetics Shop' }})
           </h5>
            <!-- Back Button -->
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
                &larr; Back
            </a>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- EXPORT + SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between flex-wrap align-items-center">
                    <!-- Export buttons -->
                <div class="btn-group mb-2">
                    <a href="{{ route('sales.export.excel', [$shop->id, $date]) }}" class="btn btn-success btn-sm">Export Excel</a>
                    <a href="{{ route('sales.export.pdf', [$shop->id, $date]) }}" class="btn btn-danger btn-sm">Export PDF</a>
                </div>


                    <!-- Search input -->
                    <div class="mb-2" style="width: 250px;">
                        <input type="text" id="table-search" class="form-control form-control-sm" placeholder="Search sale...">
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
                        <th>Action</th>
                    </thead>
                    <tbody>
                    @forelse($itemRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start">{{ $row['product'] }}</td>
                            <td>{{ $row['quantity'] }}</td>
                            <td class="fw-bold">{{ number_format($row['revenue'], 2) }}</td>
                          
                            <td class="text-start">{{ $row['staff'] }}</td>
                            <td>
                          <button class="btn btn-sm btn-info view-receipt" 
                                        data-product="{{ $row['product'] }}" 
                                        data-staff="{{ $row['staff'] }}">
                                        View
                                    </button>
                    </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
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

<!-- Optional: Table Search JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('table-search');
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


</script>

