@extends('main')

@section('title', 'POS')

@section('content')

@php
    $staffUser = auth('staff')->user();
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
])

@endsection
