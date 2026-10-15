@extends('main')

@section('title', 'Sale #' . $sale->id)

@section('content')
@php
    $soldBy = $sale->staff->full_name ?? ($sale->admin->name ?? 'N/A');
    $balance = (float) ($sale->remaining_amount ?? 0);
    $money = fn ($value) => number_format((float) $value, 2);
@endphp

<style>
@media print {
    .app-sidebar, .app-header, .no-print { display:none !important; }
    .app-main, .app-wrapper { margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:0 !important; }
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to sales
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.detail', [$sale->shop_id, $sale->created_at->toDateString()]) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-calendar-day"></i> All sales on {{ $sale->created_at->format('d M Y') }}
            </a>
            @if(auth()->user()->hasPermission('sales-returns.create') && $sale->items->sum('quantity') > 0)
                <a href="{{ route('returns.sales.create', $sale->id) }}" class="btn btn-sm btn-danger"><i class="bi bi-arrow-return-left"></i> Return items</a>
            @endif
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">Sale #{{ $sale->id }}</h5>
                    @if($balance > 0)
                        <span class="badge bg-danger">Balance due Tsh {{ $money($balance) }}</span>
                    @else
                        <span class="badge bg-success">Paid</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">Product</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Total (Tsh)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->items as $i => $item)
                                    @php $returned = (int) ($returnedQty[$item->id] ?? 0); @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="text-start">
                                            {{ $item->product->name ?? 'Deleted product' }}
                                            @if($returned)
                                                <div class="small text-danger">{{ $returned }} returned</div>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($item->sale_type ?? 'retail') }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $money($item->price) }}</td>
                                        <td>{{ (float) $item->discount > 0 ? $money($item->discount) : "—" }}</td>
                                        <td class="fw-semibold">{{ $money($item->quantity * $item->price - ($item->discount ?? 0)) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-muted py-4">No items on this sale (all returned).</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end mt-3">
                        <div class="col-md-6">
                            <table class="table table-sm mb-0">
                                <tr><td class="text-muted">Items subtotal</td><td class="text-end">{{ $money($subtotal) }}</td></tr>
                                @if($sale->bill_discount > 0)
                                    <tr><td class="text-muted">Bill discount</td><td class="text-end">− {{ $money($sale->bill_discount) }}</td></tr>
                                @endif
                                @if($sale->shipping > 0)
                                    <tr><td class="text-muted">Shipping</td><td class="text-end">{{ $money($sale->shipping) }}</td></tr>
                                @endif
                                @if(($sale->fine_amount ?? 0) > 0)
                                    <tr><td class="text-muted">Late-payment fine</td><td class="text-end">{{ $money($sale->fine_amount) }}</td></tr>
                                @endif
                                <tr class="fw-bold"><td>Total</td><td class="text-end">Tsh {{ $money($sale->total) }}</td></tr>
                                <tr><td class="text-muted">Received</td><td class="text-end">{{ $money($sale->received_amount) }}</td></tr>
                                @if(($sale->change_amount ?? 0) > 0)
                                    <tr><td class="text-muted">Change</td><td class="text-end">{{ $money($sale->change_amount) }}</td></tr>
                                @endif
                                <tr class="{{ $balance > 0 ? 'text-danger fw-semibold' : '' }}"><td>Balance</td><td class="text-end">{{ $money($balance) }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Date</dt><dd class="col-7">{{ $sale->created_at->format('d M Y, H:i') }}</dd>
                        <dt class="col-5 text-muted fw-normal">Shop</dt><dd class="col-7">{{ $sale->shop->name ?? 'N/A' }}</dd>
                        <dt class="col-5 text-muted fw-normal">Sold by</dt><dd class="col-7">{{ $soldBy }}</dd>
                        <dt class="col-5 text-muted fw-normal">Customer</dt>
                        <dd class="col-7">
                            @if($sale->customer)
                                <a href="{{ route('customers.show', $sale->customer->id) }}">{{ $sale->customer->name }}</a>
                                @if($sale->customer->phone)<div class="text-muted">{{ $sale->customer->phone }}</div>@endif
                            @else
                                Walk-in
                            @endif
                        </dd>
                        <dt class="col-5 text-muted fw-normal">Payment</dt>
                        <dd class="col-7 mb-0">
                            {{ collect([$sale->payment_type, $sale->payment_method])->filter()->map(fn ($v) => ucfirst(str_replace('_', ' ', $v)))->unique()->implode(' · ') ?: '—' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Returns</div>
                <div class="card-body">
                    @forelse($returns as $return)
                        <div class="d-flex justify-content-between small {{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                            <div>
                                <div class="fw-semibold">{{ $return->product->name ?? 'Product' }} × {{ $return->quantity }}</div>
                                <div class="text-muted">{{ ($return->returned_at ?? $return->created_at)->format('d M Y') }}{{ $return->reason ? ' · ' . $return->reason : '' }}</div>
                            </div>
                            <div class="text-danger text-nowrap">− {{ $money($return->amount) }}</div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No returns on this sale.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
