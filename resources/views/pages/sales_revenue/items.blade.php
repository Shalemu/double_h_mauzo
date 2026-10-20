@extends('main')

@section('title', 'Sale Items')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Units sold</div><div class="fs-4 fw-bold">{{ number_format($totals->quantity) }}</div>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Revenue (Tsh)</div><div class="fs-4 fw-bold">{{ number_format($totals->revenue, 2) }}</div>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Cost of goods (Tsh)</div><div class="fs-4 fw-bold">{{ number_format($totals->cost, 2) }}</div>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Gross profit (Tsh)</div>
            <div class="fs-4 fw-bold {{ $totals->revenue - $totals->cost < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($totals->revenue - $totals->cost, 2) }}</div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-basket me-2"></i>Sale Items</h5></div>
        <div class="card-body">
            @include('pages.sales_revenue._filters')

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Shop</th>
                            <th>Type</th>
                            <th>Qty sold</th>
                            <th>Selling price (Tsh)</th>
                            <th>Purchase price (Tsh)</th>
                            <th>Profit (Tsh)</th>
                            <th>Last sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php $profit = $item->revenue - $item->cost; @endphp
                            <tr>
                                <td class="text-start">{{ $item->product ?? 'Deleted product' }}</td>
                                <td>{{ $item->shop ?? 'N/A' }}</td>
                                <td>{{ ucfirst($item->sale_type ?? 'retail') }}</td>
                                <td>{{ number_format($item->quantity) }}</td>
                                <td>{{ number_format($item->selling_price, 2) }}</td>
                                <td>{{ number_format($item->purchase_price, 2) }}</td>
                                <td class="{{ $profit < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($profit, 2) }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($item->last_sold)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted py-4">No items sold in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $items->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
@endsection
