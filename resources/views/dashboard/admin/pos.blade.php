@extends('main')

@section('title', 'POS — ' . $shop->name)

@section('content')

@include('components/breadcrumb')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid pt-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2" style="max-width: 1500px; margin: auto;">
        <h5 class="mb-0"><i class="bi bi-cart-check me-2 text-success"></i>POS: {{ $shop->name }}</h5>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('admin.pos.index') }}" class="d-flex align-items-center gap-2">
                <label class="mb-0 small text-muted">Switch shop</label>
                <select name="shop" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 200px;">
                    @foreach($shops as $s)
                        <option value="{{ $s->id }}" {{ $s->id === $shop->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

</div>

@include('components.pos_core', [
    'shop' => $shop,
    'products' => $products,
    'customers' => $customers,
    'productsTotal' => $productsTotal,
    'productsDisplayLimit' => $productsDisplayLimit,
    'checkoutUrl' => route('admin.pos.checkout', ['shop' => $shop->id]),
    'customerStoreUrl' => route('admin.pos.customers.store'),
    'customerStoreShopId' => $shop->id,
])

@endsection
