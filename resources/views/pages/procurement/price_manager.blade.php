@extends('main')

@section('title', 'Price Manager')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Price Manager</h5>
            <small class="text-muted">Edit cost, retail and wholesale prices in bulk. Only the rows you change are saved.</small>
        </div>
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <form method="GET" class="row g-2 align-items-end mb-3">
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
                    <label class="form-label small mb-1">Search</label>
                    <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Name, code or barcode">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Show</label>
                    <select name="flag" class="form-select form-select-sm">
                        <option value="">All products</option>
                        <option value="below_cost" {{ ($flag === 'below_cost') ? 'selected' : '' }}>Selling below cost</option>
                        <option value="no_price" {{ ($flag === 'no_price') ? 'selected' : '' }}>Missing selling price</option>
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            <form method="POST" action="{{ route('procurement.prices.update') }}" id="priceForm">
                @csrf
                @method('PUT')
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Product</th>
                                <th>Shop</th>
                                <th>Stock</th>
                                <th style="min-width:130px">Cost (Tsh)</th>
                                <th style="min-width:130px">Retail (Tsh)</th>
                                <th style="min-width:130px">Wholesale (Tsh)</th>
                                <th>Retail margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr data-row>
                                    <td class="text-start">{{ $product->name }}<div class="small text-muted">{{ $product->item_code }}</div></td>
                                    <td>{{ $product->shop->name ?? '—' }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    @foreach(['purchase_price', 'selling_price', 'wholesale_price'] as $field)
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end price-input"
                                                   data-field="{{ $field }}" data-name="prices[{{ $product->id }}][{{ $field }}]"
                                                   data-original="{{ $product->$field }}" value="{{ $product->$field }}"
                                                   aria-label="{{ str_replace('_', ' ', $field) }} for {{ $product->name }}">
                                        </td>
                                    @endforeach
                                    <td class="margin-cell"></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted py-4">No products match.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    {{ $products->links('pagination::bootstrap-4') }}
                    <div>
                        <span class="text-muted small me-2" id="changeCount">No changes</span>
                        <button class="btn btn-success" id="saveBtn" disabled><i class="bi bi-save"></i> Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const inputs = document.querySelectorAll('.price-input');
    const saveBtn = document.getElementById('saveBtn');
    const changeCount = document.getElementById('changeCount');

    function refreshRow(row) {
        const cost = parseFloat(row.querySelector('[data-field="purchase_price"]').value) || 0;
        const sell = parseFloat(row.querySelector('[data-field="selling_price"]').value) || 0;
        const cell = row.querySelector('.margin-cell');
        if (!sell) { cell.textContent = '—'; cell.className = 'margin-cell text-muted'; return; }
        const margin = ((sell - cost) / sell) * 100;
        cell.textContent = margin.toFixed(1) + '%';
        cell.className = 'margin-cell ' + (margin < 0 ? 'text-danger fw-semibold' : 'text-success');
    }

    function refresh() {
        let changed = 0;
        inputs.forEach(function (input) {
            const dirty = String(parseFloat(input.value)) !== String(parseFloat(input.dataset.original));
            // Only changed fields are submitted, so untouched rows are never rewritten.
            if (dirty) { input.name = input.dataset.name; changed++; } else { input.removeAttribute('name'); }
            input.classList.toggle('border-primary', dirty);
        });
        saveBtn.disabled = changed === 0;
        changeCount.textContent = changed ? changed + ' change(s)' : 'No changes';
    }

    inputs.forEach(function (input) {
        input.addEventListener('input', function () { refreshRow(input.closest('[data-row]')); refresh(); });
    });
    document.querySelectorAll('[data-row]').forEach(refreshRow);
    refresh();
})();
</script>
@endsection
