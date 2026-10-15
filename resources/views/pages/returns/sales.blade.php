@extends('main')

@section('title', 'Sales Returns')

@section('content')
@php $canReturn = auth()->user()->hasPermission('sales-returns.create'); @endphp
<div class="container-fluid py-4">
    @if(session('success'))
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({ icon: 'success', title: 'Return recorded', text: @json(session('success')), confirmButtonColor: '#198754' });
        });
        </script>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Returns</div><div class="fs-4 fw-bold">{{ number_format($totals->count) }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Units returned</div><div class="fs-4 fw-bold">{{ number_format($totals->quantity) }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Refunded (Tsh)</div><div class="fs-4 fw-bold text-danger">{{ number_format($totals->amount, 2) }}</div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <h5 class="mb-0"><i class="bi bi-arrow-return-left me-2"></i>Sales Returns</h5>
                    <small class="text-muted">When a customer brings goods back: find the sale, click <strong>Return</strong>, choose how many and why. Stock goes back automatically.</small>
                </div>
            </div>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'sales' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'sales', 'history_page' => null]) }}">
                        <i class="bi bi-receipt me-1"></i> Sales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'history' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'history', 'sales_page' => null]) }}">
                        <i class="bi bi-clock-history me-1"></i> Returns history <span class="badge bg-light text-dark border ms-1">{{ number_format($totals->count) }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Shop</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All shops</option>
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
                @if($tab === 'sales')
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small mb-1">Sale # or customer</label>
                        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="e.g. 154 or Asha">
                    </div>
                @endif
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            @if($tab === 'sales')
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sale #</th>
                                <th>Date</th>
                                <th>Shop</th>
                                <th>Customer</th>
                                <th>Sold by</th>
                                <th>Items left</th>
                                <th>Total (Tsh)</th>
                                <th style="width:170px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td class="fw-semibold">#{{ $sale->id }}</td>
                                    <td class="text-nowrap">{{ $sale->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $sale->shop->name ?? '—' }}</td>
                                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                    <td>{{ $sale->staff->full_name ?? ($sale->admin->name ?? '—') }}</td>
                                    <td>{{ (int) $sale->items_left }}</td>
                                    <td>{{ number_format($sale->total, 2) }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-outline-secondary" aria-label="View sale #{{ $sale->id }}"><i class="bi bi-eye"></i></a>
                                        @if($canReturn)
                                            <a href="{{ route('returns.sales.create', $sale->id) }}" class="btn btn-sm btn-danger">
                                                <i class="bi bi-arrow-return-left"></i> Return
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-muted py-4">No sales found for this period{{ $search ? ' matching "' . $search . '"' : '' }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $sales->links('pagination::bootstrap-4') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Shop</th>
                                <th>Product</th>
                                <th>Sale #</th>
                                <th>Qty</th>
                                <th>Refund (Tsh)</th>
                                <th>Reason</th>
                                <th>Processed by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $return)
                                <tr>
                                    <td class="text-nowrap">{{ ($return->returned_at ?? $return->created_at)->format('d M Y H:i') }}</td>
                                    <td>{{ $return->shop->name ?? 'N/A' }}</td>
                                    <td class="text-start">{{ $return->product->name ?? 'Deleted product' }}</td>
                                    <td>
                                        @if($return->saleItem)
                                            <a href="{{ route('admin.sales.show', $return->saleItem->sale_id) }}">#{{ $return->saleItem->sale_id }}</a>
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
                                <tr><td colspan="8" class="text-muted py-4">No sales returns in this period.</td></tr>
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
