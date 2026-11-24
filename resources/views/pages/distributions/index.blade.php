@extends('main')

@section('title', 'Distribution')

@section('content')

<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-truck-flatbed me-2"></i>Distribution</h5>
                <small class="text-muted">A distributor takes stock out of a store. When they come back, returned units go back into the store and the rest is recorded as a sale.</small>
            </div>
            <a href="{{ route('distributions.create', array_filter(['store' => $storeId])) }}" class="btn btn-sm btn-primary {{ $stores->isEmpty() ? 'disabled' : '' }}">
                <i class="bi bi-box-arrow-right"></i> Issue stock
            </a>
        </div>
        <div class="card-body">
            @if($stores->isEmpty())
                <div class="alert alert-info">You don't have a store yet. <a href="{{ route('stores.index') }}">Add a store</a> under Manage Business → All Stores.</div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Distributors still out</div>
                        <div class="fs-4 fw-semibold">{{ $totals['out'] }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Owed by distributors (TZS)</div>
                        <div class="fs-4 fw-semibold {{ $totals['owing'] > 0 ? 'text-danger' : '' }}">{{ number_format($totals['owing'], 2) }}</div>
                    </div>
                </div>
            </div>

            <form method="GET" class="row g-2 mb-3">
                <div class="col-sm-4 col-lg-3">
                    <select name="store" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Store">
                        <option value="">All stores</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ (string) $storeId === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-lg-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Status">
                        <option value="">All statuses</option>
                        <option value="out" {{ $status === 'out' ? 'selected' : '' }}>Still out</option>
                        <option value="settled" {{ $status === 'settled' ? 'selected' : '' }}>Settled</option>
                        <option value="owing" {{ $status === 'owing' ? 'selected' : '' }}>Owing money</option>
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th>Date</th><th>Store</th><th>Distributor</th><th>Taken</th><th>Status</th><th>Sold (TZS)</th><th>Balance (TZS)</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($distributions as $d)
                            <tr>
                                <td class="text-center">{{ $d->id }}</td>
                                <td class="text-nowrap">{{ $d->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $d->store->name ?? '—' }}</td>
                                <td>{{ $d->staff->full_name ?? '—' }}</td>
                                <td>
                                    @foreach($d->items as $item)
                                        <div class="small">{{ $item->product_name }} × {{ $item->quantity_taken }}</div>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    @if($d->isOut())
                                        <span class="badge bg-warning text-dark">Out</span>
                                    @else
                                        <span class="badge bg-success">Settled</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $d->isOut() ? '—' : number_format($d->total_value, 2) }}</td>
                                <td class="text-end {{ $d->balance > 0 ? 'text-danger fw-semibold' : '' }}">{{ $d->isOut() ? '—' : number_format($d->balance, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('distributions.show', $d) }}" class="btn btn-sm {{ $d->isOut() ? 'btn-primary' : 'btn-outline-secondary' }}">
                                        {{ $d->isOut() ? 'Settle' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No distributions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $distributions->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>

@endsection
