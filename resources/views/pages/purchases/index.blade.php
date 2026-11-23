<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Purchases Report for {{ $shop->name ?? 'Shop' }}
            </h5>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('purchases.create', ['shop' => $shop->id ?? null]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-cart-plus"></i> New Purchase Order
                </a>
                <a href="{{ route('purchases.new_product', ['shop' => $shop->id ?? null]) }}" class="btn btn-info btn-sm">
                    <i class="bi bi-plus-circle"></i> New Item Purchase
                </a>
                <a href="{{ route('purchases.receiving', ['shop' => $shop->id ?? null]) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-box-arrow-in-down"></i> Receive Orders
                </a>
            </div>
        </div>

        <div class="card-body">

            <!-- SEARCH -->
            <div class="d-flex justify-content-end mb-3">
                <input type="text" id="purchases-table-search" class="form-control form-control-sm"
                       placeholder="Search date..." style="width:250px;">
            </div>

            <!-- TABLE -->
            <div id="purchases-table-container">
                <table class="table table-bordered table-sm text-center" id="purchases-table">
                    <thead class="table-primary">
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Total Paid</th>
                            <th>Remaining Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchasesByDate as $date => $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="#" class="view-date"
                                       data-url="{{ route('purchases.detail', ['shop'=>$shop->id,'date'=>$data['date']]) }}">
                                        {{ $data['date'] }}
                                    </a>
                                </td>
                                <td>{{ number_format($data['total_amount'], 2) }}</td>
                                <td>{{ number_format($data['total_paid'] ?? 0, 2) }}</td>
                                <td class="text-danger">{{ number_format($data['remaining'] ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No purchases found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PURCHASE DETAILS -->
            <div id="purchase-details" class="mt-4"></div>

        </div>
    </div>
</div>

@include('pages.purchases._index_scripts')
