@extends('main')

@section('title', 'Purchase Returns')

@section('content')
@php $canReturn = auth()->user()->hasPermission('purchase-returns.create'); @endphp
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
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <h5 class="mb-0"><i class="bi bi-box-arrow-left me-2"></i>Purchase Returns</h5>
                    <small class="text-muted">When goods go back to a supplier: find the purchase, click <strong>Return</strong>, choose how many and why. Stock is taken out and the invoice is reduced automatically.</small>
                </div>
            </div>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'purchases' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'purchases', 'history_page' => null]) }}">
                        <i class="bi bi-cart-check me-1"></i> Purchases
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'history' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'history', 'purchases_page' => null]) }}">
                        <i class="bi bi-clock-history me-1"></i> Returns history <span class="badge bg-light text-dark border ms-1">{{ number_format($totals->count) }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Shop / Store</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ (string) $shopId === (string) $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">From</label>
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">To</label>
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
                </div>
                @if($tab === 'purchases')
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small mb-1">Invoice # or supplier</label>
                        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="e.g. INV-0012 or Unilever">
                    </div>
                @endif
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            @if($tab === 'purchases')
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Shop / Store</th>
                                <th>Supplier</th>
                                <th>Products</th>
                                <th>Units left</th>
                                <th>Total (Tsh)</th>
                                <th>Payment</th>
                                <th style="width:170px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td class="fw-semibold">{{ $invoice->invoice_number ?: '#' . $invoice->id }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y H:i') }}</td>
                                    <td>{{ $invoice->shop->name ?? '—' }}</td>
                                    <td>{{ $invoice->supplier->name ?? '—' }}</td>
                                    <td>{{ (int) $invoice->lines_count }}</td>
                                    <td>{{ (int) $invoice->items_left }}</td>
                                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>
                                        @if(($invoice->payment_type ?? null) === 'credit' && $invoice->remaining_amount > 0)
                                            <span class="badge bg-warning text-dark">Credit · owes {{ number_format($invoice->remaining_amount) }}</span>
                                        @else
                                            <span class="badge bg-success">Paid</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('purchases.history', $invoice->id) }}" class="btn btn-sm btn-outline-secondary" aria-label="View invoice {{ $invoice->invoice_number ?: '#' . $invoice->id }}"><i class="bi bi-eye"></i></a>
                                        @if($canReturn)
                                            <a href="{{ route('returns.purchases.create', $invoice->id) }}" class="btn btn-sm btn-danger">
                                                <i class="bi bi-box-arrow-left"></i> Return
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-muted py-4">No purchases found for this period{{ $search ? ' matching "' . $search . '"' : '' }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $invoices->links('pagination::bootstrap-4') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Shop / Store</th>
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
            @endif
        </div>
    </div>
</div>
@endsection
