@extends('main')

@section('title', 'New Purchase Order')

@section('content')
{{-- initPurchaseForm() (components.dashboard_scripts) works inside this container. --}}
<div class="container-fluid py-4" id="add-purchase-container">
    @include('pages.purchases.create')
</div>

@include('pages.purchases._order_placed_modal')

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof initPurchaseForm === 'function') initPurchaseForm();
});
</script>
@endsection
