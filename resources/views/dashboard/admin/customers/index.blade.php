@extends('main')

@section('title', 'Customers')

@section('content')

{{-- Breadcrumb --}}
@include('components/breadcrumb')

<div class="cat__content">
    <div class="container-fluid">
        <div class="row g-4 px-4">
            <div class="col-12">
                <div class="cat__core__widget p-3 h-100 bg-white">

                    {{-- Summary Cards --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h6>Total Customers</h6>
                                    <h4 class="text-primary">{{ $customers->count() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h6>Total Paid (All Shops)</h6>
                                    <h4 class="text-success">{{ number_format($customers->sum('total_paid')) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h6>Total Outstanding Debt</h6>
                                    <h4 class="text-danger">{{ number_format($customers->sum('total_debt')) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Top Bar --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <input type="text"
                               id="customerSearch"
                               class="form-control form-control-sm w-25"
                               placeholder="Search customer...">

                        <a href="{{ route('dashboard.admin') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>

                    {{-- Customers Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center staff-table">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Shop</th>
                                    <th>Total Purchases</th>
                                    <th>Total Paid</th>
                                    <th>Outstanding Debt</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $index => $customer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('customers.show', $customer->id) }}" class="text-primary fw-bold">
                                                {{ $customer->name }}
                                            </a>
                                        </td>
                                        <td>{{ $customer->phone ?? '-' }}</td>
                                        <td>{{ $customer->shop->name ?? '-' }}</td>
                                        <td>{{ number_format($customer->total_purchases ?? 0) }}</td>
                                        <td>{{ number_format($customer->total_paid ?? 0) }}</td>
                                        <td>{{ number_format($customer->total_debt ?? 0) }}</td>
                                        <td>
                                            @if(($customer->total_debt ?? 0) > 0)
                                                <span class="badge bg-danger">In Debt</span>
                                            @else
                                                <span class="badge bg-success">Clear</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">No customers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div><br><br><br><br>
</div>

{{-- Search --}}
<script>
document.getElementById('customerSearch').addEventListener('keyup', function () {
    let filter = this.value.toUpperCase();
    document.querySelectorAll('.staff-table tbody tr').forEach(row => {
        row.style.display =
            row.textContent.toUpperCase().includes(filter) ? '' : 'none';
    });
});
</script>

@endsection
