{{-- Day's returns opened directly (not loaded into the shop page): same content, in the app layout. --}}
@extends('main')

@section('title', 'Sales returns on ' . \Carbon\Carbon::parse($date)->format('d M Y'))

@section('content')
    @include('pages.sales_returns.detail')
@endsection
