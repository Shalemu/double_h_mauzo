<div class="container-fluid mt-4">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <div class="card-header bg-white">
            <h5>
                Daily Purchase Detail for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                ({{ $shop->name }})
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>S/N</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Supplier</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($itemRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start">{{ $row['product'] }}</td>
                            <td>{{ $row['quantity'] }}</td>
                            <td>{{ number_format($row['total'],2) }}</td>
                            <td>{{ $row['supplier'] }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm return-purchase-btn"
                                    data-purchase-item-id="{{ $row['purchase_item_id'] ?? '' }}"
                                    data-product-id="{{ $row['product_id'] ?? '' }}"
                                    data-product-name="{{ $row['product'] }}"
                                    data-quantity="{{ $row['quantity'] }}"
                                    data-price="{{ $row['quantity'] > 0 ? $row['total'] / $row['quantity'] : 0 }}">
                                    Return
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">No data found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
