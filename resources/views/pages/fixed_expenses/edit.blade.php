@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header">
            <h5>Edit Fixed Expense</h5>
        </div>

        <div class="card-body">

            <form action="{{ route('fixed-expenses.update', $expense->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control"
                           value="{{ $expense->title }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control"
                           value="{{ $expense->amount }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control">{{ $expense->note }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Update Expense
                </button>

                <a href="{{ route('fixed-expenses.index', $shop->id) }}"
                   class="btn btn-secondary">
                   Cancel
                </a>

            </form>

        </div>
    </div>

</div>

@endsection