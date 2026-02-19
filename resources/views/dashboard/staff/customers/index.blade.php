@extends('main')
@section('title', 'Manage Customers')

@include('components/staff_header')
@include('components/mainmenu')

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
                            <th>Email</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                           <td>
                            <a href="{{ route('staff.customers.show', $customer->id) }}" class="text-primary fw-bold">
                                {{ $customer->name }}
                            </a>
                        </td>

                            <td>{{ $customer->phone ?? '-' }}</td>
                            <td>{{ $customer->email ?? '-' }}</td>
                            <td>{{ $customer->address ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">No customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('staff.customers.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <label>Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="mb-2">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    let table = $('#customerTable').DataTable({
        responsive: true,
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        dom: 'rtip'
    });

    $('#customerSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#entriesSelect').on('change', function() {
        let val = parseInt($(this).val());
        table.page.len(val > 0 ? val : table.data().length).draw();
    });

});
</script>
