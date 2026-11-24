@extends('main')

@section('title', 'Receive Orders')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-box-arrow-in-down me-2"></i>Receive Orders
                    <span class="badge bg-warning text-dark">{{ number_format($orders->total()) }}</span>
                </h5>
                <small class="text-muted">Orders placed with suppliers and still waiting for delivery. Receiving one adds its stock.</small>
            </div>
            @if(auth()->user()->hasPermission('purchases.create'))
                <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> New Purchase Order</a>
            @endif
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1" for="rcvShop">Shop / Store</label>
                    <select name="shop" id="rcvShop" class="form-select form-select-sm">
                        <option value="">All shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ (string) $shopId === (string) $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1" for="rcvSupplier">Supplier</label>
                    <select name="supplier" id="rcvSupplier" class="form-select form-select-sm">
                        <option value="">All suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (string) $supplierId === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Order</th>
                            <th>Ordered</th>
                            <th class="text-start">Supplier</th>
                            <th class="text-start">Shop / Store</th>
                            <th>Lines</th>
                            <th>Units ordered</th>
                            <th class="text-end">Order value (Tsh)</th>
                            <th>Payment</th>
                            <th style="width:190px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php $waitingDays = $order->ordered_at ? (int) $order->ordered_at->diffInDays(now()) : null; @endphp
                            <tr>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('purchases.history', $order->id) }}">{{ \App\Helpers\InvoiceNumber::for($order) }}</a>
                                    {{-- The number above is the supplier's invoice number when one was typed;
                                         then also show the system's own reference. --}}
                                    @if($order->invoice_number)<div class="small text-muted">PI-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>@endif
                                </td>
                                <td class="text-center text-nowrap">
                                    {{ optional($order->ordered_at)->format('d M Y') }}
                                    @if($waitingDays !== null)
                                        <div class="small {{ $waitingDays > 7 ? 'text-danger' : 'text-muted' }}">
                                            {{ $waitingDays === 0 ? 'today' : $waitingDays . ' day(s) ago' }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $order->supplier->name ?? '—' }}</td>
                                <td>{{ $order->shop->name ?? '—' }}</td>
                                <td class="text-center">{{ $order->items_count }}</td>
                                <td class="text-center">{{ number_format($order->ordered_units) }}</td>
                                <td class="text-end">{{ number_format($order->total_amount, 2) }}</td>
                                <td class="text-center">{{ ucfirst($order->payment_type) }}</td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('purchases.receive', $order->id) }}" class="btn btn-success btn-sm"><i class="bi bi-box-arrow-in-down"></i> Receive</a>
                                    @if(auth()->user()->hasPermission('purchases.create'))
                                        <form action="{{ route('purchases.cancel', $order->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Cancel order {{ \App\Helpers\InvoiceNumber::for($order) }}? Nothing has been stocked or paid.')">
                                            @csrf
                                            <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-check2-circle me-1"></i> No orders waiting to be received.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">@if($orders->total()) Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ number_format($orders->total()) }} @endif</small>
                {{ $orders->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
