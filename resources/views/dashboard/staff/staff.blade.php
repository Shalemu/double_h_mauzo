
@php
$shops = $shops ?? collect();
@endphp

@extends('main')

@section('title', 'Staff Dashboard')

@section('content')

    {{-- Breadcrumb --}}
    @include('components/breadcrumb', ['shops' => $shops])

    {{-- Main Menu --}}
    @include('components/mainmenu', ['shops' => $shops])

    <br><br><br><br><br>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="cat__content">
        <div class="container-fluid">
            <div class="row g-4" style="padding-left:30px; padding-right:30px;">
                <div class="col-xl-10 mx-auto">
                    <div class="cat__core__widget p-3 h-100" style="background:#fff;">

                        <!-- Top Bar -->
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

                            <!-- Left: Search & Export -->
                            <div class="d-flex align-items-center mb-2">
                                <div style="margin-right:8px;">
                                    <input type="text" id="staffSearch" class="form-control form-control-sm" placeholder="Search staff...">
                                </div>
                                <div style="margin-right:8px;">
                                    <button class="btn btn-sm btn-success">Export Excel</button>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-danger">Export PDF</button>
                                </div>
                            </div>

                            <!-- Right: Back & Add Staff -->
                            <div class="d-flex align-items-center mb-2">
                                <div style="margin-right:8px;">
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Back</a>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                        Add Staff
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- Staff Table -->
                        <table class="table table-bordered text-center w-75 mx-auto staff-table">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Shop</th>
                                    <th>Wages</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>John Doe</td>
                                    <td>0712345678</td>
                                    <td>Main Shop</td>
                                    <td>120000</td>
                                    <td>2026-01-28</td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>0756789012</td>
                                    <td>Branch Shop</td>
                                    <td>100000</td>
                                    <td>2026-01-27</td>
                                    <td>
                                        <button class="btn btn-sm btn-info">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="3"><strong>Total Wages</strong></td>
                                    <td><strong>220000</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= ADD STAFF MODAL ================= -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog modal-lg" style="margin-top:160px;">
            <div class="modal-content">

                <form method="POST" action="{{ route('staff.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Register Staff</h5>
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

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <input type="text" name="phone" class="form-control" placeholder="Phone" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control" placeholder="Email">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
<select name="shop_id" class="form-control" required>
    <option value="">Select Shop</option>
    @foreach($shops as $shop)
        <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>
            {{ $shop->name }}
        </option>
    @endforeach
</select>

                            </div>
                            <div class="col-md-6">
      <select name="role_id" class="form-control" required>
    <option value="">Select Role</option>
    @foreach ($roles as $role)
        <option value="{{ $role->id }}"
            {{ old('role_id') == $role->id ? 'selected' : '' }}>
            {{ ucfirst($role->name) }}
        </option>
    @endforeach
</select>


                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Staff</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- SEARCH SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('staffSearch');
            if(searchInput){
                searchInput.addEventListener('keyup', function () {
                    const filter = this.value.toUpperCase();
                    document.querySelectorAll('.staff-table tbody tr').forEach(row => {
                        if(row.classList.contains('table-success')) return;
                        const nameCell = row.querySelector('td:first-child');
                        row.style.display = nameCell && nameCell.textContent.toUpperCase().includes(filter) ? '' : 'none';
                    });
                });
            }
        });
    </script>

    <!-- REQUIRED BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection
