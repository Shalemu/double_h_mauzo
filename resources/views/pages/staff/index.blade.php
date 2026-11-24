@extends('main')

@section('title', 'POS')

@section('content')

@php
    $staffUser = \App\Models\Staff::current();
    $trip = $trip ?? null;           // set when a distributor sells from their trip (trip products only)
    // On a trip the sale is from the trip's store (where the stock came from).
    $shop = $trip ? $trip->store : ($shop ?? $staffUser->shop);
    $products = $products ?? collect();
    $customers = $customers ?? collect();
    $productsTotal = $productsTotal ?? $products->count();
    $productsDisplayLimit = $productsDisplayLimit ?? null;
@endphp

@if($trip)
    <div class="container-fluid mt-3">
        <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
            <div>
                <i class="bi bi-truck-flatbed me-1"></i>
                <strong>Selling from your trip #{{ $trip->id }}</strong> ({{ $trip->store->name ?? 'store' }}).
                Stock shown is what you still carry; each sale is recorded at once.
            </div>
            <a href="{{ route('staff.trip') }}" class="btn btn-sm btn-primary"><i class="bi bi-list-check"></i> Trip &amp; sales</a>
        </div>
    </div>
@endif

@include('components.pos_core', [
    'shop' => $shop,
    'products' => $products,
    'customers' => $customers,
    'productsTotal' => $productsTotal,
    'productsDisplayLimit' => $productsDisplayLimit,
    'checkoutUrl' => $trip
        ? route('staff.trip.checkout', ['distribution' => $trip->id])
        : route('staff.sales.checkout', ['shop' => $shop->id]),
    'customerStoreUrl' => route('staff.customers.store'),
    'customerStoreShopId' => null,
    // Everything a distributor carries is already on the page: no server search needed.
    'productSearchUrl' => $trip ? '' : route('staff.pos.search'),
])

@endsection
