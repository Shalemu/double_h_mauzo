@extends('main')

@section('title', 'Trip')

@section('content')
@php
    $money = fn ($v) => number_format((float) $v, 2);
@endphp
<div class="container-fluid py-4">

    @if(!$trip)
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-truck-flatbed fs-1 text-muted"></i>
                <h5 class="mt-2">No trip out right now</h5>
                <p class="text-muted mb-0">When your supervisor issues stock to you, it appears here and on your POS.</p>
            </div>
        </div>
    @else
        @php
            $salesTotal = $sales->sum('total');
            $cash = $sales->sum(fn ($s) => $s->received_amount - $s->change_amount);
            $credit = $sales->sum('remaining_amount');
            $unitsLeft = $trip->items->sum->quantity_left;
            $unitsSold = $trip->items->sum('quantity_recorded');
        @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-0"><i class="bi bi-truck-flatbed me-2"></i>Trip #{{ $trip->id }} · {{ $trip->store->name ?? 'store' }}</h5>
                <small class="text-muted">Out since {{ $trip->created_at->format('d M Y H:i') }}. Your supervisor settles it when you're back.</small>
            </div>
            <a href="{{ route('staff.dashboard') }}" class="btn btn-primary"><i class="bi bi-cash-coin"></i> Sell (POS)</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3"><div class="card shadow-sm h-100"><div class="card-body">
                <div class="small text-muted">Sold on this trip</div><div class="fs-4 fw-bold">Tsh {{ $money($salesTotal) }}</div>
                <div class="small text-muted">{{ $sales->count() }} sale(s) · {{ number_format($unitsSold) }} unit(s)</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card shadow-sm h-100"><div class="card-body">
                <div class="small text-muted">Cash you collected</div><div class="fs-4 fw-bold text-success">Tsh {{ $money($cash) }}</div>
                <div class="small text-muted">hand this in when you're back</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card shadow-sm h-100"><div class="card-body">
                <div class="small text-muted">Given on credit</div><div class="fs-4 fw-bold text-danger">Tsh {{ $money($credit) }}</div>
                <div class="small text-muted">owed by customers</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card shadow-sm h-100"><div class="card-body">
                <div class="small text-muted">Still with you</div><div class="fs-4 fw-bold">{{ number_format($unitsLeft) }}</div>
                <div class="small text-muted">unit(s) to sell or bring back</div>
            </div></div></div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>What you carry</h6></div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center"><tr><th class="text-start">Product</th><th>Taken</th><th>Sold</th><th>Left</th></tr></thead>
                    <tbody>
                        @foreach($trip->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td class="text-center">{{ $item->quantity_taken }}</td>
                                <td class="text-center">{{ $item->quantity_recorded }}</td>
                                <td class="text-center fw-semibold {{ $item->quantity_left === 0 ? 'text-muted' : '' }}">{{ $item->quantity_left }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Your sales on this trip</h6></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center"><tr><th>#</th><th>Time</th><th class="text-start">Customer</th><th>Items</th><th>Payment</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Credit</th></tr></thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td class="text-center">{{ $sale->id }}</td>
                                <td class="text-center text-nowrap">{{ $sale->created_at->format('d M H:i') }}</td>
                                <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                <td class="text-center">{{ $sale->items->sum('quantity') }}</td>
                                <td class="text-center">{{ $sale->payment_type ?: ucfirst($sale->payment_method) }}</td>
                                <td class="text-end">{{ $money($sale->total) }}</td>
                                <td class="text-end">{{ $money($sale->received_amount - $sale->change_amount) }}</td>
                                <td class="text-end {{ $sale->remaining_amount > 0 ? 'text-danger' : '' }}">{{ $money($sale->remaining_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No sales yet on this trip.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Past trips</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-center"><tr><th>Trip</th><th class="text-start">Store</th><th>Settled</th><th>POS sales</th><th class="text-end">Total sold</th><th class="text-end">Handed in</th><th class="text-end">You still owe</th></tr></thead>
                <tbody>
                    @forelse($pastTrips as $past)
                        <tr>
                            <td class="text-center">#{{ $past->id }}</td>
                            <td>{{ $past->store->name ?? '—' }}</td>
                            <td class="text-center text-nowrap">{{ optional($past->settled_at)->format('d M Y') }}</td>
                            <td class="text-center">{{ $past->pos_sales_count }}</td>
                            <td class="text-end">{{ $money($past->total_value) }}</td>
                            <td class="text-end">{{ $money($past->amount_received) }}</td>
                            <td class="text-end {{ $past->balance > 0 ? 'text-danger fw-semibold' : 'text-success' }}">{{ $money($past->balance) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No past trips.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
