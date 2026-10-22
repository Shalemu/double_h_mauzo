@extends('main')

@section('title', 'Supplier Products')

@section('content')
@php
    $canAssign = auth()->user()->hasPermission('suppliers.edit');
    $canPrice = auth()->user()->hasPermission('prices.edit');
    $money = fn ($v) => $v !== null ? number_format((float) $v, 2) : '—';
@endphp
<style>
.assign-panel { border:1px solid #f0dcdd; border-radius:.8rem; background:#fdf9f9; padding:1rem 1.15rem; }
.assign-stat { border:1px solid #e5e7eb; border-radius:.5rem; background:#fff; padding:.4rem .65rem; height:100%; }
.assign-stat span { display:block; font-size:.72rem; color:#6b7280; }
.assign-stat strong { font-size:.95rem; }
.price-diff { font-size:.72rem; }
</style>

<div class="container-fluid py-4">
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Supplier Products</h5>
                    <small class="text-muted">Which supplier supplies which product, and at what price.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('procurement.prices') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-tags"></i> Price Manager</a>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-truck"></i> Suppliers</a>
                    @if($canAssign)
                        <button type="button" class="btn btn-sm btn-primary" id="toggleAssign" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="assignPanel">
                            <i class="bi bi-link-45deg"></i> Assign product to supplier
                        </button>
                    @endif
                </div>
            </div>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'assigned' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'assigned', 'page' => null]) }}">
                        <i class="bi bi-link-45deg me-1"></i> Assigned products <span class="badge bg-light text-dark border ms-1">{{ number_format($assigned->total()) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'history' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'history', 'assigned_page' => null]) }}">
                        <i class="bi bi-clock-history me-1"></i> From purchases
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            @if($canAssign)
                {{-- Assign a product: pick supplier + product; its purchasing price loads by itself. --}}
                <form method="POST" action="{{ route('procurement.supplierProducts.assign') }}" class="assign-panel mb-4" id="assignPanel" {{ $errors->any() ? '' : 'hidden' }}>
                    @csrf
                    <input type="hidden" name="product_id" id="assignProductId" value="{{ old('product_id') }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-semibold" id="assignTitle"><i class="bi bi-link-45deg me-1"></i> Assign product to supplier</h6>
                        <button type="button" class="btn-close" id="closeAssign" aria-label="Close"></button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small" for="assignSupplier">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="assignSupplier" class="form-select" required>
                                <option value="">-- select supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (string) old('supplier_id', $supplierId) === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small" for="assignShop">Shop / Store <span class="text-danger">*</span></label>
                            <select id="assignShop" class="form-select">
                                <option value="">-- select shop / store --</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ (string) ($shopId ?: session('active_shop_id')) === (string) $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small" for="assignProduct">Product <span class="text-danger">*</span></label>
                            <select id="assignProduct" class="form-select" disabled>
                                <option value="">Select a shop first</option>
                            </select>
                            <input type="search" id="assignSearch" class="form-control form-control-sm mt-1" autocomplete="off" placeholder="Filter the list or scan barcode..." aria-label="Filter products">
                        </div>
                    </div>

                    <div id="assignDetails" hidden>
                        <div class="row g-2 mt-2">
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Shop / Store</span><strong id="aShop">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Unit</span><strong id="aUnit">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>In stock</span><strong id="aStock">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Current purchasing price</span><strong id="aCost">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Retail / Wholesale</span><strong id="aSell">—</strong></div></div>
                        </div>
                        <div class="row g-3 align-items-end mt-1">
                            <div class="col-md-4">
                                <label class="form-label small" for="assignNewPrice">New price <span class="text-muted">(optional — if the price changed)</span></label>
                                <input type="number" name="new_price" id="assignNewPrice" class="form-control" min="0" step="any" value="{{ old('new_price') }}" placeholder="Leave empty to keep current">
                            </div>
                            <div class="col-md-5">
                                @if($canPrice)
                                    <div class="small text-muted mb-1"><i class="bi bi-info-circle"></i> Saving also sets this as the product's purchasing price.</div>
                                    <a href="#" class="small" id="assignPriceManager" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Open this product in Price Manager</a>
                                @else
                                    <div class="small text-muted"><i class="bi bi-info-circle"></i> The purchasing price stays as it is — you don't have permission to change prices.</div>
                                @endif
                            </div>
                            <div class="col-md-3 text-md-end">
                                <button class="btn btn-primary w-100" id="assignSubmit" disabled><i class="bi bi-check-lg"></i> Save</button>
                            </div>
                        </div>
                        <div class="small text-muted mt-2" id="assignSummary"></div>
                    </div>
                </form>
            @endif

            <form method="GET" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Supplier</label>
                    <select name="supplier" class="form-select form-select-sm">
                        <option value="">All suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ ((string) $supplierId === (string) $supplier->id) ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Shop / Store</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ ((string) $shopId === (string) $shop->id) ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Product</label>
                    <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search product...">
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            @if($tab === 'assigned')
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Supplier</th>
                                <th class="text-start">Product</th>
                                <th>Unit</th>
                                <th>Supplier price (Tsh)</th>
                                <th>Our purchasing price</th>
                                <th>Retail</th>
                                <th>Wholesale</th>
                                <th>In stock</th>
                                <th style="width:160px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assigned as $row)
                                @php $diff = (float) $row->supplier_price - (float) $row->current_cost; @endphp
                                <tr>
                                    <td class="text-start">{{ $row->supplier }}<div class="small text-muted">{{ $row->phone }}</div></td>
                                    <td class="text-start">{{ $row->product }}<div class="small text-muted">{{ $row->shop ?? '—' }}</div></td>
                                    <td><span class="badge bg-secondary">{{ $row->unit ?? '—' }}</span></td>
                                    <td class="fw-semibold">{{ $money($row->supplier_price) }}</td>
                                    <td>
                                        {{ $money($row->current_cost) }}
                                        @if(abs($diff) >= 0.01)
                                            <div class="price-diff {{ $diff > 0 ? 'text-danger' : 'text-success' }}">
                                                Supplier {{ $diff > 0 ? 'higher' : 'lower' }} by {{ number_format(abs($diff), 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $money($row->selling_price) }}</td>
                                    <td>{{ $money($row->wholesale_price) }}</td>
                                    <td>{{ number_format((int) $row->in_stock) }}</td>
                                    <td class="text-nowrap">
                                        @if($canAssign)
                                            <button type="button" class="btn btn-sm btn-outline-primary js-edit-assignment" aria-label="Change price"
                                                    data-supplier="{{ $row->supplier_id }}"
                                                    data-product="{{ json_encode(['id' => $row->product_id, 'name' => $row->product, 'shop_id' => $row->shop_id, 'shop' => $row->shop, 'unit' => $row->unit, 'quantity' => (int) $row->in_stock, 'purchase_price' => $row->current_cost, 'selling_price' => $row->selling_price, 'wholesale_price' => $row->wholesale_price]) }}"
                                                    data-price="{{ $row->supplier_price }}"><i class="bi bi-pencil"></i></button>
                                        @endif
                                        @if($canPrice && abs($diff) >= 0.01)
                                            <form method="POST" action="{{ route('procurement.supplierProducts.applyPrice', $row->id) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" title="Use supplier price as purchasing price" aria-label="Apply supplier price"><i class="bi bi-arrow-down-circle"></i></button>
                                            </form>
                                        @endif
                                        @if($canAssign)
                                            <form method="POST" action="{{ route('procurement.supplierProducts.remove', $row->id) }}" class="d-inline"
                                                  onsubmit="return confirm('Remove {{ addslashes($row->product) }} from {{ addslashes($row->supplier) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-muted py-4">
                                    No products assigned yet.
                                    @if($canAssign) Click <strong>Assign product to supplier</strong> to link one. @endif
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $assigned->links('pagination::bootstrap-4') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Supplier</th>
                                <th>Product</th>
                                <th>Deliveries</th>
                                <th>Total qty bought</th>
                                <th>Cost range (Tsh)</th>
                                <th>Current cost</th>
                                <th>Selling price</th>
                                <th>In stock</th>
                                <th>Last purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td class="text-start">{{ $row->supplier }}<div class="small text-muted">{{ $row->phone }}</div></td>
                                    <td class="text-start">{{ $row->product ?? 'Deleted product' }}</td>
                                    <td>{{ $row->deliveries }}</td>
                                    <td>{{ number_format($row->total_qty) }}</td>
                                    <td class="text-nowrap">
                                        {{ number_format($row->min_cost, 2) }}@if($row->max_cost != $row->min_cost) – {{ number_format($row->max_cost, 2) }}@endif
                                    </td>
                                    <td>{{ $row->current_cost !== null ? number_format($row->current_cost, 2) : '—' }}</td>
                                    <td>{{ $row->selling_price !== null ? number_format($row->selling_price, 2) : '—' }}</td>
                                    <td>{{ $row->in_stock ?? '—' }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($row->last_purchased)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-muted py-4">
                                    No supplier deliveries found. Products appear here once a purchase is recorded against a supplier.
                                    <a href="{{ route('purchases.create') }}">Record a purchase</a>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $rows->links('pagination::bootstrap-4') }}</div>
            @endif
        </div>
    </div>
</div>

@if($canAssign)
@include('pages.procurement._supplier_products_scripts')
@endif
@endsection
