<!-- MAIN CONTENT -->
<div class="container-fluid mt-4 main-content">

    <!-- SALES REPORT CARD -->
    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Report for {{ $shop->name ?? 'Double H Cosmetics Shop' }}</h5>
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
                        <button type="button" class="btn btn-success btn-sm" id="export-excel">Export Excel</button>
                        <button type="button" class="btn btn-danger btn-sm" id="export-pdf">Export PDF</button>
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
                            <th style="width:5%;">SN</th>
                            <th style="width:30%;">Date</th>
                            <th style="width:20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesByDate as $date => $saleData)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            
                            <td>
                            <a href="javascript:void(0)" class="view-date" data-url="{{ route('sales.detail', ['shop' => $shop->id, 'date' => $saleData['date']]) }}" style="text-decoration: none;">
                                {{ $saleData['date'] }}
                            </a>
                        </td>

                            </td>
                            <td>{{ number_format($saleData['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No sales for this date.</td>
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

document.querySelectorAll('.view-date').forEach(link => {
    link.addEventListener('click', function() {
        const url = this.dataset.url;
        fetch(url)
            .then(res => res.text())
            .then(html => {
                document.getElementById('sale-section').innerHTML = html;
            });
    });
});

</script>
