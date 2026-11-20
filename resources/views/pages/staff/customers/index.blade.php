@extends('main')
@section('title', 'Manage Customers')

@section('content')

<div class="container-fluid mt-5">

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>

        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fa fa-plus"></i> Add Customer
        </button>
    </div>

    <!-- Customer List Card -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0">Customer List</h5>
        </div>

        <div class="card-body">

            <!-- Top Controls -->
            <div class="d-flex justify-content-between mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0">Show
                        <select id="entriesSelect" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                        entries
                    </label>
                </div>
                <div>
                    <input type="search" id="customerSearch" class="form-control form-control-sm w-auto" placeholder="Search Customers">
                </div>
            </div>

            <!-- Customer Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center" id="customerTable">
                    <thead class="table-light">
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Birthday</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $index => $customer)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                           <td>
                            <a href="{{ route('staff.customers.show', $customer->id) }}" class="text-primary fw-bold">
                                {{ $customer->name }}
                            </a>
                        </td>

                            <td>{{ $customer->phone ?? '-' }}</td>
                            <td>{{ $customer->date_of_birth?->format('d M') ?? '-' }}</td>
                            <td>{{ $customer->email ?? '-' }}</td>
                            <td>{{ $customer->address ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('staff.customers.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body py-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label mb-1">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Date of Birth </label>
                        <input type="date" name="date_of_birth" class="form-control" max="{{ now()->subDay()->toDateString() }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label mb-1">Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>

@include('pages.staff.customers._index_scripts')

@endsection
