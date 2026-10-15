@extends('main')

@section('title', 'Purchases')

@section('content')

@include('pages.purchases.index', [
    'shop' => $shop,
    'purchases' => $purchases ?? collect(),
    'purchasesByDate' => $purchasesByDate ?? collect(),
])

@endsection
