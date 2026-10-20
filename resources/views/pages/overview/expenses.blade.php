@extends('main')

@section('title', 'Expenses')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Expenses</h5>
                <small class="text-muted">Day-to-day spending. Total for this period: <strong>Tsh {{ number_format($total, 2) }}</strong></small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><i class="bi bi-plus-circle"></i> Add expense</button>
        </div>
        <div class="card-body">
            @include('pages.overview._shop_filter')

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Shop</th><th class="text-start">Title</th><th>Amount (Tsh)</th><th>Note</th><th>Receipt</th><th>Recorded by</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="text-nowrap">{{ $expense->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $expense->shop->name ?? '—' }}</td>
                                <td class="text-start">{{ $expense->title }}</td>
                                <td>{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->note ?: '—' }}</td>
                                <td>
                                    @if($expense->receipt)
                                        <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank" rel="noopener">View</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $expense->creator_name }}</td>
                                <td>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}" onsubmit="return confirm('Delete this expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $expense->title }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted py-4">No expenses in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $expenses->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>

@include('pages.overview._add_expense_modal', [
    'modalId' => 'addExpenseModal',
    'heading' => 'Add expense',
    'routeName' => 'expenses.store',
    'withReceipt' => true,
])
@endsection
