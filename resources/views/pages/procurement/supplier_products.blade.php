@extends('main')

@section('title', 'Supplier Products')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Supplier Products</h5>
                <small class="text-muted">What each supplier has delivered, from recorded purchase invoices.</small>
            </div>
            <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-truck"></i> Suppliers</a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
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
                    <label class="form-label small mb-1">Shop</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All shops</option>
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
        </div>
    </div>
</div>
@endsection
