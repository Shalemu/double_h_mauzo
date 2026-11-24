@extends('main')

@section('title', 'Purchases')

@section('content')

{{-- Orders placed but not yet delivered: receiving one adds its stock. --}}
@if(($pendingOrders ?? collect())->isNotEmpty())
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-warning">
        <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Pending orders <span class="badge bg-warning text-dark">{{ $pendingOrders->count() }}</span></h5>
            <small class="text-muted">Not in stock or totals until received</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Ordered</th>
                            <th>Supplier</th>
                            <th>Lines</th>
                            <th>Units ordered</th>
                            <th>Order value (Tsh)</th>
                            <th>Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingOrders as $order)
                            <tr>
                                <td><a href="{{ route('purchases.history', $order->id) }}">{{ \App\Helpers\InvoiceNumber::for($order) }}</a></td>
                                <td class="text-nowrap">{{ optional($order->ordered_at)->format('d M Y H:i') }}</td>
                                <td>{{ $order->supplier->name ?? '—' }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td>{{ number_format($order->ordered_units) }}</td>
                                <td>{{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ ucfirst($order->payment_type) }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('purchases.receive', $order->id) }}" class="btn btn-success btn-sm"><i class="bi bi-box-arrow-in-down"></i> Receive</a>
                                    <form action="{{ route('purchases.cancel', $order->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Cancel this order? Nothing has been stocked or paid.')">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@include('pages.purchases.index', [
    'shop' => $shop,
    'purchases' => $purchases ?? collect(),
    'purchasesByDate' => $purchasesByDate ?? collect(),
])

@include('pages.purchases._order_placed_modal')

@endsection
