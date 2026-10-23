@extends('main')
@section('title', 'Sales Report')

@section('content')

<div class="container-fluid mt-5 main-content">

    <!-- MAIN SALES CARD -->
    <div class="card shadow-sm border" style="max-width: 1400px; margin: 0 auto;">
        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Report for {{ $shop->name ?? 'My Shop' }}</h5>
        </div>

        <div class="card-body">

            <!-- SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <input type="text" id="table-search" class="form-control form-control-sm"
                           placeholder="Search date..." style="width: 250px;">
                </div>
            </div>

            <!-- SALES TABLE -->
            <div class="table-responsive" id="sales-table-container">
                <table class="table table-bordered table-hover table-sm text-center" id="sales-table">
                    <thead class="table-primary">
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Total (TZS)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesByDate as $sale)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="javascript:void(0)" class="view-date"
                                   data-url="{{ route('staff.sales.detail', ['shop' => $shop->id, 'date' => $sale['date']]) }}">
                                    {{ $sale['date'] }}
                                </a>
                            </td>
                            <td>{{ number_format($sale['total'], 2) }}</td>
                            <td>
                                <button class="btn btn-success btn-sm checkout-btn"
                                    data-url="{{ route('staff.sales.checkout', ['shop' => $shop->id]) }}"
                                    data-date="{{ $sale['date'] }}">
                                    Checkout
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No sales found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- SALES DETAILS CARD (AJAX loaded) -->
            <div id="sales-details" class="mt-4" style="display:none;">
                <!-- AJAX content will appear here -->
            </div>

        </div>
    </div>
</div>

<!-- JS -->
@include('pages.staff.sales._index_scripts')

@endsection