{{-- Shop + date-range filter shared by the Sales & Revenue pages. --}}
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
    <div class="col-6 col-md-2">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
    </div>
    @isset($search)
        <div class="col-sm-6 col-md-3">
            <label class="form-label small mb-1">Product</label>
            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search product...">
        </div>
    @endisset
    <div class="col-auto">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button>
    </div>
</form>
