@extends('main')

@section('title', 'Order Requests')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-bag-check me-2"></i>Order Requests</h5>

            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <select name="shop" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All shops</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ ((string) $shopId === (string) $shop->id) ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <ul class="nav nav-pills mb-3 flex-wrap gap-1">
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'purchased' => 'Purchased', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ $status === $key ? 'active' : '' }}"
                           href="{{ route('admin.orders.index', ['status' => $key, 'shop' => $shopId]) }}">
                            {{ $label }}
                            <span class="badge {{ $status === $key ? 'bg-light text-dark' : 'bg-secondary' }} ms-1">
                                {{ $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0) }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Shop</th>
                            <th>Requested by</th>
                            <th>Items</th>
                            <th>Total (Tsh)</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->shop->name ?? 'N/A' }}</td>
                                <td>{{ $order->staff->full_name ?? 'N/A' }}</td>
                                <td>{{ $order->items->count() }}</td>
                                <td>{{ number_format($order->total_amount, 2) }}</td>
                                <td>@include('components.order_status_badge', ['status' => $order->status])</td>
                                <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> {{ $order->status === 'pending' ? 'Review' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted py-4">No {{ $status === 'all' ? '' : $status }} order requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $orders->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
@endsection
