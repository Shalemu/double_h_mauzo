<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Sale Returns for {{ $shop->name }}</h5>
                <small class="text-muted">Select a date below to view its sold items and process a return.</small>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>S/N</th>
                            <th>Date</th>
                            <th>Total Sold</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($salesByDate as $date => $saleData)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="javascript:void(0)"
                                   class="view-date"
                                   data-url="{{ route('admin.sales_returns.detail', [
                                       'shop' => $shop->id,
                                       'date' => $saleData['date']
                                   ]) }}">
                                    {{ $saleData['date'] }}
                                </a>
                            </td>
                            <td>{{ number_format($saleData['total'],2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No sales found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- AJAX CONTENT -->
    <div id="sales-return-section"></div>

</div>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('pages.sales_returns._index_scripts')
