<!-- MAIN CONTENT -->
<div class="container-fluid mt-4">

    <!-- SALES REPORT CARD -->
    <div class="card shadow-sm w-100">

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
                        <button type="button" class="btn btn-success btn-sm" data-export="excel" data-export-table="#sales-table" data-export-title="{{ ($shop->name ?? 'Shop') }} - Sales"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
                        <button type="button" class="btn btn-danger btn-sm" data-export="pdf" data-export-table="#sales-table" data-export-title="{{ ($shop->name ?? 'Shop') }} - Sales"><i class="bi bi-file-earmark-pdf"></i> Export PDF</button>
                    </div>

                    <!-- Search input -->
                    <div class="mb-2" style="width: 250px;">
                        <input type="text" id="sales-table-search" class="form-control form-control-sm" placeholder="Search sale...">
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
                            <a href="javascript:void(0)" class="view-date" data-url="{{ route('admin.sales.detail', ['shop' => $shop->id, 'date' => $saleData['date']]) }}" style="text-decoration: none;">
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
@include('pages.sales._index_scripts')

@include('components.table_export')
