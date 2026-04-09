@extends('main')

@section('title', 'Order Details')

@section('content')

@include('components.staff_header')
@include('components.mainmenu')

<div class="container-fluid mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order #{{ $order->id }} Details</h5>

            <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>

        <div class="card-body">

            <div class="mb-3">
                <strong>Staff:</strong> {{ $order->staff->first_name ?? 'N/A' }} {{ $order->staff->last_name ?? '' }} <br>
                <strong>Status:</strong>
                @if($order->status == 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                @elseif($order->status == 'approved')
                    <span class="badge bg-success">Approved</span>
                @else
                    <span class="badge bg-danger">Rejected</span>
                @endif
                <br>
                <strong>Total Amount:</strong> Tsh {{ number_format($order->items->sum('total'), 2) }} <br>
                <strong>Created At:</strong> {{ $order->created_at->format('d M Y H:i') }}
            </div>

            <hr>

            <h6>Items</h6>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Sale Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>Tsh {{ number_format($item->price, 2) }}</td>
                                <td>Tsh {{ number_format($item->total, 2) }}</td>
                                <td>{{ ucfirst($item->sale_type) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

@endsection