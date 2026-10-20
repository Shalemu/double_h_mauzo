@extends('main')

@section('title', 'Credit')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Customers owe you (Tsh)</div>
            <div class="fs-4 fw-bold text-primary">{{ number_format($customers->sum('owed'), 2) }}</div>
            <div class="small text-muted">{{ $customers->count() }} customer(s)</div>
        </div></div></div>
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">You owe suppliers (Tsh)</div>
            <div class="fs-4 fw-bold text-danger">{{ number_format($invoices->sum('remaining_amount'), 2) }}</div>
            <div class="small text-muted">{{ $invoices->count() }} unpaid invoice(s)</div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'customers' ? 'active' : '' }}" href="{{ route('admin.credit', ['tab' => 'customers', 'shop' => $shopId]) }}">
                        <i class="bi bi-people me-1"></i> Customer credit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'suppliers' ? 'active' : '' }}" href="{{ route('admin.credit', ['tab' => 'suppliers', 'shop' => $shopId]) }}">
                        <i class="bi bi-truck me-1"></i> Supplier credit
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            @include('pages.overview._shop_filter', ['keep' => ['tab' => $tab]])

            @if($tab === 'customers')
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr><th class="text-start">Customer</th><th>Phone</th><th>Unpaid sales</th><th>Owed (Tsh)</th><th>Oldest debt</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td class="text-start">{{ $customer->name }}</td>
                                    <td>{{ $customer->phone ?: '—' }}</td>
                                    <td>{{ $customer->open_sales }}</td>
                                    <td class="fw-semibold">{{ number_format($customer->owed, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($customer->oldest)->format('d M Y') }}
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($customer->oldest)->diffForHumans() }}</div></td>
                                    <td>
                                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-cash"></i> View / record payment
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted py-4">No customer owes anything.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr><th>Invoice</th><th>Supplier</th><th>Shop</th><th>Purchased</th><th>Total (Tsh)</th><th>Paid (Tsh)</th><th>Balance (Tsh)</th><th style="min-width:220px">Pay</th></tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td><a href="{{ route('purchases.history', $invoice->id) }}">{{ $invoice->invoice_number ?: '#' . $invoice->id }}</a></td>
                                    <td>{{ $invoice->supplier->name ?? '—' }}</td>
                                    <td>{{ $invoice->shop->name ?? '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->purchased_at ?? $invoice->created_at)->format('d M Y') }}</td>
                                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>{{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="fw-semibold text-danger">{{ number_format($invoice->remaining_amount, 2) }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('purchases.deposit') }}" class="d-flex gap-1">
                                            @csrf
                                            <input type="hidden" name="purchase_invoice_id" value="{{ $invoice->id }}">
                                            <input type="number" name="amount" class="form-control form-control-sm" min="1" step="0.01"
                                                   max="{{ $invoice->remaining_amount }}" placeholder="Amount" required
                                                   aria-label="Payment amount for {{ $invoice->invoice_number }}">
                                            <button class="btn btn-sm btn-success">Pay</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-muted py-4">No unpaid supplier invoices.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
