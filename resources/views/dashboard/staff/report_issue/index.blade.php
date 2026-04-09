@extends('main')

@section('title', 'Create Order')

@section('content')

@include('components.staff_header')
@include('components.mainmenu')
<div class="container mt-4">

    <div class="card shadow-sm" style="max-width: 700px; margin:auto;">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Report Shop Issue</h5>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('staff.report.issue.store') }}" method="POST">
                @csrf

                {{-- Issue Type --}}
                <div class="mb-3">
                    <label class="form-label">Issue Type</label>
                    <select name="type" class="form-control" required>
                        <option value="">Select issue</option>
                        <option value="stock">Stock Problem</option>
                        <option value="system">System Error</option>
                        <option value="customer">Customer Issue</option>
                        <option value="finance">Finance Issue</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="message" class="form-control" rows="5"
                        placeholder="Explain the issue clearly..." required></textarea>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        Back
                    </a>

                    <button type="submit" class="btn btn-danger">
                        Submit Report
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection