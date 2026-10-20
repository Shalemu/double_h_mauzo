{{-- Day summary opened directly (not loaded into the shop page): same content, in the app layout. --}}
@extends('main')

@section('title', 'Sales on ' . \Carbon\Carbon::parse($date)->format('d M Y'))

@section('content')
    @include('pages.sales.detail')
@endsection
