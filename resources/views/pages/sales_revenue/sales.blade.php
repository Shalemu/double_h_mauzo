@extends('main')

@section('title', 'Sales')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Sales</div><div class="fs-4 fw-bold">{{ number_format($totals->count) }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Total sold (Tsh)</div><div class="fs-4 fw-bold">{{ number_format($totals->total, 2) }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Unpaid / credit (Tsh)</div><div class="fs-4 fw-bold text-danger">{{ number_format($totals->credit, 2) }}</div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Sales</h5></div>
        <div class="card-body">
            @include('pages.sales_revenue._filters')

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Shop</th>
                            <th>Sold by</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Total (Tsh)</th>
                            <th>Balance (Tsh)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td class="text-nowrap">{{ $sale->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $sale->shop->name ?? 'N/A' }}</td>
                                <td>{{ $sale->staff->full_name ?? ($sale->admin->name ?? 'N/A') }}</td>
                                <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                <td>{{ $sale->items_count }}</td>
                                <td>{{ ucfirst($sale->payment_type ?? $sale->payment_method ?? '—') }}</td>
                                <td>{{ number_format($sale->total, 2) }}</td>
                                <td class="{{ $sale->remaining_amount > 0 ? 'text-danger' : '' }}">{{ number_format($sale->remaining_amount ?? 0, 2) }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary text-nowrap" href="{{ route('admin.sales.show', $sale->id) }}"
                                       aria-label="View sale #{{ $sale->id }}">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-muted py-4">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $sales->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
@endsection
