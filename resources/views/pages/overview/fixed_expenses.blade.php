@extends('main')

@section('title', 'Fixed Expenses')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-calendar2-check me-2"></i>Fixed Expenses</h5>
                <small class="text-muted">Recurring costs such as rent, salaries and utilities. Total: <strong>Tsh {{ number_format($fixedExpenses->sum('amount'), 2) }}</strong></small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFixedExpenseModal"><i class="bi bi-plus-circle"></i> Add fixed expense</button>
        </div>
        <div class="card-body">
            @include('pages.overview._shop_filter')

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr><th>Shop</th><th class="text-start">Title</th><th>Amount (Tsh)</th><th>Note</th><th>Added</th><th style="width:110px"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($fixedExpenses as $expense)
                            <tr>
                                <td>{{ $expense->shop->name ?? '—' }}</td>
                                <td class="text-start">{{ $expense->title }}</td>
                                <td>{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->note ?: '—' }}</td>
                                <td>{{ $expense->created_at->format('d M Y') }}</td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFixed{{ $expense->id }}" aria-label="Edit {{ $expense->title }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('fixed-expenses.destroy', $expense->id) }}" class="d-inline" onsubmit="return confirm('Delete this fixed expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $expense->title }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted py-4">No fixed expenses recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($fixedExpenses as $expense)
    <div class="modal fade" id="editFixed{{ $expense->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('fixed-expenses.update', $expense->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit fixed expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{ $expense->title }}" required></div>
                    <div class="mb-3"><label class="form-label">Amount (Tsh)</label><input type="number" name="amount" class="form-control" min="0" step="0.01" value="{{ $expense->amount }}" required></div>
                    <div class="mb-3"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2">{{ $expense->note }}</textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@include('pages.overview._add_expense_modal', [
    'modalId' => 'addFixedExpenseModal',
    'heading' => 'Add fixed expense',
    'routeName' => 'fixed-expenses.store',
    'withReceipt' => false,
])
@endsection
