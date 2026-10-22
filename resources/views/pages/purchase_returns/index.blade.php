<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Purchase Returns for {{ $shop->name }}</h5>
                <small class="text-muted">Select a date below to view its purchased items and process a return.</small>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>S/N</th>
                            <th>Date</th>
                            <th>Total Purchased</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($purchasesByDate as $date => $purchaseData)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="javascript:void(0)"
                                   class="view-purchase-date"
                                   data-url="{{ route('admin.purchase_returns.detail', [
                                       'shop' => $shop->id,
                                       'date' => $purchaseData['date']
                                   ]) }}">
                                    {{ $purchaseData['date'] }}
                                </a>
                            </td>
                            <td>{{ number_format($purchaseData['total_amount'],2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- AJAX CONTENT -->
    <div id="purchase-return-detail"></div>

</div>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('pages.purchase_returns._index_scripts')
