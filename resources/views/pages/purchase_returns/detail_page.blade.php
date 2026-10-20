{{-- Day's purchases opened directly (not loaded into the shop page): same content, in the app layout. --}}
@extends('main')

@section('title', 'Purchase returns on ' . \Carbon\Carbon::parse($date)->format('d M Y'))

@section('content')
    @include('pages.purchase_returns.detail')
@endsection
