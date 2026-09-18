@section('title', 'Dashboard')
@include('main')
@include('components/staff_header')
@include('components/mainmenu')

@php
    $staffUser = auth('staff')->user();
    $shop = $shop ?? $staffUser->shop;
    $products = $products ?? collect();
    $customers = $customers ?? collect();
    $productsTotal = $productsTotal ?? $products->count();
    $productsDisplayLimit = $productsDisplayLimit ?? null;
@endphp


<meta name="csrf-token" content="{{ csrf_token() }}">


<div class="cat__content">


<!-- TOP ACTION BUTTONS -->
<div class="row mb-4">
<div class="col-12 d-flex flex-wrap" style="gap:12px;padding-left:20px;">

    <button class="btn btn-outline-danger">
        <i class="bi bi-cart-plus"></i> Summary
    </button>

    <!-- <button class="btn btn-outline-success">
        <i class="bi bi-bag-plus text-success"></i> Purchases
    </button> -->



     <a href="{{ route('staff.expenses.index', $shop->id) }}" class="btn btn-outline-warning">
        <i class="bi bi-cash-stack"></i> Expenses
    </a>


      <a href="{{ route('staff.sales.index', $shop->id) }}" class="btn btn-outline-primary">
        <i class="bi bi-shop"></i> Sales
    </a>

    <a href="{{ route('staff.products.index') }}" class="btn btn-outline-info">
        <i class="bi bi-box-seam "></i> Items
    </a>
    <a href="{{ route('staff.customers.manage') }}" class="btn btn-outline-secondary">
    <i class="bi bi-people"></i> Customers
    </a>
    <a href="{{ route('staff.orders.index') }}" class="btn btn-outline-success">
        <i class="bi bi-cash-stack"></i> Orders
    </a>
    <a href="{{ route('staff.report.issue.index') }}" class="btn btn-outline-danger">
        <i class="bi bi-exclamation-circle"></i> Report Issue
    </a>
</div>

</div>

@include('components.pos_core', [
    'shop' => $shop,
    'products' => $products,
    'customers' => $customers,
    'productsTotal' => $productsTotal,
    'productsDisplayLimit' => $productsDisplayLimit,
    'checkoutUrl' => route('staff.sales.checkout', ['shop' => $shop->id]),
    'customerStoreUrl' => route('staff.customers.store'),
    'customerStoreShopId' => null,
])

</div>
