@extends('main')

@section('title', 'Stock Transfers')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Stock Transfers</h5>
                <small class="text-muted">Move stock between shops and stores (store → store, store → shop, shop → shop). The receiving side's matching product (same barcode or name) is topped up, or created if it doesn't stock it yet.</small>
            </div>
            <a href="{{ route('stock-transfers.create') }}" class="btn btn-sm btn-primary {{ ($shops->count() < 2) ? 'disabled' : '' }}">
                <i class="bi bi-plus-circle"></i> New transfer
            </a>
        </div>
        <div class="card-body">
            @if($shops->count() < 2)
                <div class="alert alert-info">You need at least two shops or stores to transfer stock. <a href="{{ route('dashboard.shop') }}">Add a shop or store</a>.</div>
            @endif

            @include('pages.overview._shop_filter')

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th>Date</th><th>From</th><th>To</th><th>Items</th><th>Units</th><th>By</th><th>Note</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td class="text-center">{{ $transfer->id }}</td>
                                <td class="text-nowrap">{{ $transfer->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $transfer->fromShop->label ?? '—' }}</td>
                                <td>{{ $transfer->toShop->label ?? '—' }}</td>
                                <td>
                                    @foreach($transfer->items as $item)
                                        <div class="small">{{ $item->product_name }} × {{ $item->quantity }}</div>
                                    @endforeach
                                </td>
                                <td class="text-center">{{ $transfer->items->sum('quantity') }}</td>
                                <td>{{ $transfer->transferredBy->name ?? '—' }}</td>
                                <td>{{ $transfer->note ?: '—' }}</td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('stock-transfers.show', $transfer) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('stock-transfers.show', [$transfer, 'print' => 1]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" aria-label="Print transfer #{{ $transfer->id }}">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No stock transfers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $transfers->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>

@endsection
