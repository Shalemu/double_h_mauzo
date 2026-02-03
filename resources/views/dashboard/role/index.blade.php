@extends('main')

@section('title', 'Role Dashboard')

@section('content')

{{-- Breadcrumb --}}
@include('components/breadcrumb')

{{-- Main Menu --}}
@include('components/mainmenu')

<br><br><br><br><br>

<div class="cat__content">
    <div class="container-fluid">
        <div class="row g-4 px-4">
            <div class="col-xl-10 mx-auto">
                <div class="cat__core__widget p-3 h-100 bg-white">

                    {{-- Top Bar --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        {{-- Search --}}
                        <input type="text"
                               id="roleSearch"
                               class="form-control form-control-sm w-25"
                               placeholder="Search role...">

                        {{-- Actions --}}
                        <div>
                            <a href="{{ url()->previous() }}"
                               class="btn btn-secondary btn-sm me-2">
                                Back
                            </a>

                            <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addRoleModal">
                                Add Role
                            </button>
                        </div>
                    </div>

                    {{-- Roles Table --}}
                    <table class="table table-bordered text-center staff-table mx-auto w-75">
                        <thead class="table-warning text-uppercase">
                            <tr>
                                <th>Role Name</th>
                                <th>Description</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ ucfirst($role->name) }}</td>
                                <td>{{ $role->description ?? '—' }}</td>
<td>
    <div class="d-flex justify-content-center">
        {{-- Edit --}}
        <button class="btn btn-sm btn-outline-primary"
                style="margin-right: 8px;"   {{-- force space --}}
                data-bs-toggle="modal"
                data-bs-target="#editRoleModal{{ $role->id }}">
            <i class="bi bi-pencil"></i>
        </button>

        {{-- Delete --}}
        <form action="{{ route('dashboard.roles.destroy', $role->id) }}" method="POST" class="d-inline" style="margin-left: 0;">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </div>
</td>


                            </tr>

                            {{-- Edit Role Modal --}}
                            <div class="modal fade"
                                 id="editRoleModal{{ $role->id }}"
                                 tabindex="-1">
                                <div class="modal-dialog modal-md" style="margin-top: 200px;">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('dashboard.roles.update', $role->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Role</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Role Name</label>
                                                    <input type="text"
                                                           name="name"
                                                           class="form-control"
                                                           value="{{ $role->name }}"
                                                           required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description"
                                                              class="form-control"
                                                              rows="3">{{ $role->description }}</textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-success">Update</button>
                                                <button type="button"
                                                        class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="3">No roles found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div><br><br><br><br>


{{-- ADD ROLE MODAL --}}
<div class="modal fade" id="addRoleModal" tabindex="-1" style="">
    <div class="modal-dialog modal-md" style="margin-top: 200px;">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.roles.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Save Role</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Auto open modal on error --}}
@if(session('success') || $errors->any())
<script>
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('addRoleModal')).show();
});
</script>
@endif

{{-- Search --}}
<script>
document.getElementById('roleSearch').addEventListener('keyup', function () {
    let filter = this.value.toUpperCase();
    document.querySelectorAll('.staff-table tbody tr').forEach(row => {
        row.style.display =
            row.textContent.toUpperCase().includes(filter) ? '' : 'none';
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection
