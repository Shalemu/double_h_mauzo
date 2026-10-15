@extends('main')

@section('title', 'Purchase Returns')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Returns</div><div class="fs-4 fw-bold">{{ number_format($totals->count) }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Units sent back</div><div class="fs-4 fw-bold">{{ number_format($totals->quantity) }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Credited by suppliers (Tsh)</div><div class="fs-4 fw-bold text-success">{{ number_format($totals->amount, 2) }}</div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-box-arrow-left me-2"></i>Purchase Returns</h5>
                <small class="text-muted">Goods sent back to suppliers. Recording one removes the stock and reduces the invoice (and any credit owed).</small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#recordReturnModal">
                <i class="bi bi-plus-circle"></i> Record return
            </button>
        </div>
        <div class="card-body">
            @include('pages.sales_revenue._filters')

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Shop</th>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Invoice</th>
                            <th>Qty</th>
                            <th>Amount (Tsh)</th>
                            <th>Reason</th>
                            <th>Processed by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            @php $invoice = $return->purchaseItem->invoice ?? null; @endphp
                            <tr>
                                <td class="text-nowrap">{{ ($return->returned_at ?? $return->created_at)->format('d M Y H:i') }}</td>
                                <td>{{ $return->shop->name ?? 'N/A' }}</td>
                                <td class="text-start">{{ $return->product->name ?? 'Deleted product' }}</td>
                                <td>{{ $invoice->supplier->name ?? '—' }}</td>
                                <td>
                                    @if($invoice)
                                        <a href="{{ route('purchases.history', $invoice->id) }}">{{ $invoice->invoice_number ?: '#' . $invoice->id }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $return->quantity }}</td>
                                <td>{{ number_format($return->amount, 2) }}</td>
                                <td>{{ $return->reason ?: '—' }}</td>
                                <td>{{ $return->processedBy->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-muted py-4">No purchase returns in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $returns->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>

@include('pages.returns._record_modal', ['type' => 'purchase'])
@endsection
