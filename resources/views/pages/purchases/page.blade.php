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

{{-- Right after an order is placed: download / share / email it. --}}
@if(session('purchase_invoice_id') && ($savedInvoice = \App\Models\PurchaseInvoice::withOrders()->with('supplier', 'shop', 'items')->find(session('purchase_invoice_id'))))
    @php $savedNumber = \App\Helpers\InvoiceNumber::for($savedInvoice); @endphp
    <div class="modal fade" id="purchaseDoneModal" tabindex="-1" aria-labelledby="purchaseDoneTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseDoneTitle"><i class="bi bi-check-circle-fill text-success me-2"></i>Purchase order placed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Order</span><strong>{{ $savedNumber }}</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Supplier</span><span>{{ $savedInvoice->supplier->name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Units ordered</span><span>{{ number_format($savedInvoice->items->sum('ordered_quantity')) }}</span></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Order value</span><strong>Tsh {{ number_format($savedInvoice->total_amount, 2) }}</strong></div>

                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        This order is pending. Stock is added when you receive it and confirm what arrived.
                    </div>

                    <div class="alert {{ session('purchase_invoice_emailed') ? 'alert-success' : 'alert-warning' }} small py-2">
                        <i class="bi {{ session('purchase_invoice_emailed') ? 'bi-envelope-check' : 'bi-envelope-exclamation' }} me-1"></i>
                        {{ session('purchase_invoice_email_note') }}
                    </div>

                    @include('pages.purchases._invoice_share', ['invoice' => $savedInvoice])
                </div>
                <div class="modal-footer">
                    <a href="{{ route('purchases.history', $savedInvoice->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> View order</a>
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('purchaseDoneModal')).show();
    });
    </script>
@endif

@endsection
