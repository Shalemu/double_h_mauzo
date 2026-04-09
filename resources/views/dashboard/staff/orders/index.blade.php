@extends('main')

@section('title', 'Orders')

@section('content')

@include('components.staff_header')
@include('components.mainmenu')

<div class="container-fluid mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Orders</h5>

            <a href="{{ route('staff.orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Order
            </a>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $index => $order)
                            @php
                                $total = $order->items->sum('total');
                                $count = $order->items->count();
                            @endphp

                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>{{ $count }}</td>

                                <td>Tsh {{ number_format($total, 2) }}</td>

                                <td>
                                    @if($order->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($order->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>

                                <td>{{ $order->created_at->format('d M Y') }}</td>

                                <td>
                                    <a href="{{ route('staff.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                        View
                                    </a>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7">No orders found</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

@endsection