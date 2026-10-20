@extends('main')

@section('title', $store->name)

@section('content')
<style>
.store-stat { border:1px solid #e5e7eb; border-radius:.75rem; padding:.85rem 1rem; background:#fff; height:100%; }
.store-stat span { display:block; font-size:.78rem; color:#6b7280; }
.store-stat strong { font-size:1.35rem; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="text-muted mb-0">
            <i class="bi bi-geo-alt"></i> {{ $store->location }}
            @if($store->isSuspended()) · <span class="badge bg-secondary">Suspended</span>@endif
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('stores.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> All Stores</a>
            <a href="{{ route('stock-transfers.create', ['shop' => $store->id]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-left-right"></i> Transfer stock</a>
            <a href="{{ route('distributions.index', ['store' => $store->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-truck-flatbed"></i> Distribution</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg"><div class="store-stat"><span>Products</span><strong>{{ number_format($summary['products']) }}</strong></div></div>
        <div class="col-6 col-lg"><div class="store-stat"><span>Units in store</span><strong>{{ number_format($summary['units']) }}</strong></div></div>
        <div class="col-6 col-lg"><div class="store-stat"><span>Stock value (TZS)</span><strong>{{ number_format($summary['value'], 2) }}</strong></div></div>
        <div class="col-6 col-lg"><div class="store-stat"><span>Units with distributors</span><strong class="{{ $summary['out_units'] ? 'text-warning' : '' }}">{{ number_format($summary['out_units']) }}</strong></div></div>
        <div class="col-12 col-lg"><div class="store-stat"><span>Owed by distributors (TZS)</span><strong class="{{ $summary['owed'] > 0 ? 'text-danger' : '' }}">{{ number_format($summary['owed'], 2) }}</strong></div></div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0"><i class="bi bi-box-seam me-1"></i> Products in this store</h6>
            <form method="GET" class="d-flex gap-2">
                <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name or barcode..." aria-label="Search products" style="min-width:220px">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                @if($search !== '')
                    <a href="{{ route('stores.show', $store) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Product</th>
                            <th>Purchasing (TZS)</th>
                            <th>Retail (TZS)</th>
                            <th>Wholesale (TZS)</th>
                            <th title="On the shelf now">Available in store</th>
                            <th title="Taken by distributors on trips not yet settled">With distributors</th>
                            <th title="In store + with distributors">Total stock</th>
                            <th title="Given out on settled trips (recorded as sales)">Sold</th>
                            <th title="Brought back on settled trips — already added back to the store">Returned</th>
                            <th>Stock value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            @php
                                $m = $movement->get($product->id);
                                $out = (int) ($m->out_qty ?? 0);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    @if($product->barcode)<small class="text-muted">{{ $product->barcode }}</small>@endif
                                </td>
                                <td class="text-end">{{ number_format($product->purchase_price, 2) }}</td>
                                <td class="text-end">{{ number_format($product->selling_price, 2) }}</td>
                                <td class="text-end">{{ number_format($product->wholesale_price, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $product->quantity > 0 ? 'bg-success' : 'bg-danger' }} fs-6">{{ $product->quantity }}</span>
                                </td>
                                <td class="text-center {{ $out ? 'text-warning fw-semibold' : 'text-muted' }}">{{ $out }}</td>
                                <td class="text-center fw-semibold">{{ $product->quantity + $out }}</td>
                                <td class="text-center">{{ (int) ($m->sold_qty ?? 0) }}</td>
                                <td class="text-center">{{ (int) ($m->returned_qty ?? 0) }}</td>
                                <td class="text-end">{{ number_format($product->quantity * $product->purchase_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    {{ $search !== '' ? 'No products match your search.' : 'This store has no products yet. Buy stock into it from Purchases, or transfer stock in.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $products->links('pagination::bootstrap-4') }}</div>
            <p class="small text-muted mt-2 mb-0">
                When a distributor takes stock it leaves <em>Available in store</em> and shows under <em>With distributors</em>.
                When the trip is settled, returned units go back into <em>Available in store</em> and the rest count as <em>Sold</em>.
            </p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-truck-flatbed me-1"></i> Recent distribution trips</h6>
            <a href="{{ route('distributions.index', ['store' => $store->id]) }}" class="small">See all</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th>Date</th><th>Distributor</th><th>Taken</th><th>Returned</th><th>Sold</th><th>Status</th><th>Owed (TZS)</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($distributions as $d)
                            <tr>
                                <td class="text-center">{{ $d->id }}</td>
                                <td class="text-nowrap">{{ $d->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $d->staff->full_name ?? '—' }}</td>
                                <td class="text-center">{{ $d->items->sum('quantity_taken') }}</td>
                                <td class="text-center">{{ $d->isOut() ? '—' : $d->items->sum('quantity_returned') }}</td>
                                <td class="text-center">{{ $d->isOut() ? '—' : $d->items->sum(fn ($i) => $i->quantity_sold) }}</td>
                                <td class="text-center">
                                    @if($d->isOut())
                                        <span class="badge bg-warning text-dark">Out</span>
                                    @else
                                        <span class="badge bg-success">Settled</span>
                                    @endif
                                </td>
                                <td class="text-end {{ $d->balance > 0 ? 'text-danger fw-semibold' : '' }}">{{ $d->isOut() ? '—' : number_format($d->balance, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('distributions.show', $d) }}" class="btn btn-sm {{ $d->isOut() ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $d->isOut() ? 'Settle' : 'View' }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-3">No distribution trips from this store yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
