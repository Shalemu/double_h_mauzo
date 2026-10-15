@extends('main')

@section('title', 'Manage Product')

@section('content')

@include('pages.products.index', [
    'products' => $products,
    'categories' => $categories,
    'units' => $units,
    'shops' => $shops ?? collect(),
    'isAdminTier' => $isAdminTier ?? false,
    'productsTotal' => $productsTotal ?? null,
    'productsDisplayLimit' => $productsDisplayLimit ?? null,
    'activeShop' => $activeShop ?? null,
    'globalMode' => $globalMode ?? false,
    'productFilter' => $productFilter ?? 'all',
])

@endsection
