@extends('main')

@section('title', 'Suppliers')

@section('content')
@php
    $canDo = fn ($permission) => auth()->user()->hasPermission($permission);
    $showActions = $canDo('suppliers.edit') || $canDo('suppliers.delete');
@endphp

<div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="cat__core__widget p-3 h-100 bg-white">
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

                    {{-- Top Bar --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <input type="text"
                               id="supplierSearch"
                               class="form-control form-control-sm w-25"
                               placeholder="Search supplier...">

                        @if($canDo('suppliers.create'))
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                <i class="bi bi-person-plus"></i> Add Supplier
                            </button>
                        @endif
                    </div>

                    {{-- Suppliers Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center supplier-table">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Purchases</th>
                                    @if($showActions)<th style="width:110px">Action</th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $index => $supplier)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $supplier->name }}</td>
                                        <td>{{ $supplier->phone ?? '-' }}</td>
                                        <td>{{ $supplier->email ?? '-' }}</td>
                                        <td>{{ $supplier->address ?? '-' }}</td>
                                        <td>{{ $supplier->invoices_count }}</td>
                                        @if($showActions)
                                            <td class="text-nowrap">
                                                @if($canDo('suppliers.edit'))
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSupplierModal"
                                                            data-action="{{ route('suppliers.update', $supplier->id) }}" data-name="{{ $supplier->name }}"
                                                            data-phone="{{ $supplier->phone }}" data-email="{{ $supplier->email }}" data-address="{{ $supplier->address }}"
                                                            aria-label="Edit {{ $supplier->name }}"><i class="bi bi-pencil"></i></button>
                                                @endif
                                                @if($canDo('suppliers.delete'))
                                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" class="d-inline"
                                                          onsubmit="return confirm('Delete supplier {{ addslashes($supplier->name) }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $supplier->name }}"
                                                                {{ $supplier->invoices_count ? 'disabled' : '' }}
                                                                title="{{ $supplier->invoices_count ? 'Has purchase history, cannot be deleted' : 'Delete' }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">No suppliers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
</div>

{{-- Edit Supplier Modal --}}
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow-lg rounded-4" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editSupplierTitle"><i class="bi bi-pencil me-2"></i> Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Supplier Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('editSupplierModal').addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const form = this.querySelector('form');
    form.action = trigger.dataset.action;
    ['name', 'phone', 'email', 'address'].forEach(function (field) {
        form.querySelector('[name=' + field + ']').value = trigger.dataset[field] || '';
    });
});
</script>

{{-- Add Supplier Modal --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierModalLabel">
                    <i class="bi bi-person-plus me-2"></i> Add New Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="supplierForm" method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter supplier name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Supplier phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Supplier email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Supplier address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Search --}}
<script>
document.getElementById('supplierSearch').addEventListener('keyup', function () {
    let filter = this.value.toUpperCase();
    document.querySelectorAll('.supplier-table tbody tr').forEach(row => {
        row.style.display =
            row.textContent.toUpperCase().includes(filter) ? '' : 'none';
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const supplierForm = document.getElementById('supplierForm');

    supplierForm.addEventListener('submit', function (e) {
        e.preventDefault();

        fetch("{{ route('suppliers.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(supplierForm)
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                throw new Error(data.message || (data.errors && Object.values(data.errors)[0][0]) || 'Failed to save supplier');
            }

            let modal = bootstrap.Modal.getInstance(document.getElementById('addSupplierModal'));
            if (modal) modal.hide();

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Supplier added successfully'
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Failed to save supplier'
            });
        });
    });
});
</script>

@endsection
