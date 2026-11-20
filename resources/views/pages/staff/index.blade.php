@extends('main')

@section('title', 'POS')

@section('content')

@php
    $staffUser = \App\Models\Staff::current();
    $shop = $shop ?? $staffUser->shop;
    $products = $products ?? collect();
    $customers = $customers ?? collect();
    $productsTotal = $productsTotal ?? $products->count();
    $productsDisplayLimit = $productsDisplayLimit ?? null;
@endphp

@include('components.pos_core', [
    'shop' => $shop,
    'products' => $products,
    'customers' => $customers,
    'productsTotal' => $productsTotal,
    'productsDisplayLimit' => $productsDisplayLimit,
    'checkoutUrl' => route('staff.sales.checkout', ['shop' => $shop->id]),
    'customerStoreUrl' => route('staff.customers.store'),
    'customerStoreShopId' => null,
    'productSearchUrl' => route('staff.pos.search'),
])

@endsection
