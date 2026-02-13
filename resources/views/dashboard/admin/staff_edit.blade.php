@extends('main')

@section('title', 'Edit Staff')

@include('components/breadcrumb', ['shops' => $shops])
@include('components/mainmenu', ['shops' => $shops])

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Employee Details</h2>
        <small class="text-muted">Update your employee information</small>
    </div>

    {{-- Display Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('staff.update', $staff->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Profile Information -->
            <div class="col-lg-6">
                <div class="card h-100 border-light shadow-sm">
                    <div class="card-header bg-light">
                        <i class="bi bi-person"></i> Profile Information
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="{{ asset('images/default-avatar.png') }}" alt="Avatar" class="rounded-circle mb-3" width="100">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $staff->first_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $staff->last_name) }}">
                            </div>

                            <div class="col-md-6">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}">
                            </div>

                            <div class="col-md-6">
                                <label>Joined</label>
                                <input type="text" class="form-control" value="{{ $staff->created_at->format('M d, Y') }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label>Shop</label>
                                <select name="shop_id" class="form-control">
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}" @if($staff->shop_id == $shop->id) selected @endif>{{ $shop->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions -->
            <div class="col-lg-6">
                <div class="card h-100 border-light shadow-sm">
                    <div class="card-header bg-light">
                        <i class="bi bi-shield-lock"></i> Roles & Permissions
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Role</label>
                                <select name="role_id" class="form-control">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @if($staff->role_id == $role->id) selected @endif>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Wages</label>
                                <input type="number" name="wages" class="form-control" value="{{ old('wages', $staff->wages) }}">
                            </div>

                            <div class="col-md-6">
                                <label>Password (leave blank to keep current)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save & Back Buttons -->
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Save Changes
            </button>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </form>
</div>
@endsection
