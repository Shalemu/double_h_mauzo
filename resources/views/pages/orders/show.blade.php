@extends('main')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #{{ $order->id }} @include('components.order_status_badge', ['status' => $order->status])</h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>In stock now</th>
                                    <th>Requested qty</th>
                                    <th>Price (Tsh)</th>
                                    <th>Total (Tsh)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="text-start">{{ $item->product->name ?? 'Deleted product' }}</td>
                                        <td>{{ $item->product->quantity ?? '—' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->price, 2) }}</td>
                                        <td>{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end">Total</td>
                                    <td>{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Shop</dt><dd>{{ $order->shop->name ?? 'N/A' }}</dd>
                        <dt>Requested by</dt><dd>{{ $order->staff->full_name ?? 'N/A' }}{{ $order->staff?->phone ? ' · ' . $order->staff->phone : '' }}</dd>
                        <dt>Requested at</dt><dd>{{ $order->created_at->format('d M Y H:i') }}</dd>
                        <dt>Staff note</dt><dd>{{ $order->note ?: '—' }}</dd>
                        @if($order->reviewed_at)
                            <dt>Reviewed</dt><dd>{{ $order->reviewer->name ?? 'Admin' }}, {{ $order->reviewed_at->format('d M Y H:i') }}</dd>
                            <dt>Admin note</dt><dd>{{ $order->admin_note ?: '—' }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @php
                $canApprove = auth()->user()->hasPermission('order-requests.approve');
                $canMarkPurchased = auth()->user()->hasPermission('order-requests.purchased');
                $canDecide = ($order->status === 'pending' && $canApprove)
                    || ($order->status === 'approved' && ($canApprove || $canMarkPurchased));
            @endphp
            @if(in_array($order->status, ['pending', 'approved']) && $canDecide)
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold">Decision</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">
                            @csrf
                            @method('PUT')
                            <label class="form-label">Note to staff (optional)</label>
                            <textarea name="admin_note" class="form-control mb-3" rows="3" maxlength="1000"></textarea>

                            <div class="d-grid gap-2">
                                @if($order->status === 'pending')
                                    <button name="status" value="approved" class="btn btn-success"><i class="bi bi-check2-circle"></i> Approve</button>
                                @elseif($canMarkPurchased)
                                    @if(auth()->user()->hasPermission('purchases.create'))
                                        <a href="{{ route('purchases.create') }}" class="btn btn-outline-primary"><i class="bi bi-cart-plus"></i> Record the purchase</a>
                                    @endif
                                    <button name="status" value="purchased" class="btn btn-success"><i class="bi bi-bag-check"></i> Mark as purchased</button>
                                @endif
                                @if($canApprove)
                                    <button name="status" value="rejected" class="btn btn-outline-danger"
                                            onclick="return confirm('Reject this order request?')"><i class="bi bi-x-circle"></i> Reject</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
