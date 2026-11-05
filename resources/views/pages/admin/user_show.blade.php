@extends('main')

@section('title', 'User Details')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">User Details</h4>
        <a href="{{ route('users.manage.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- ================= DETAILS ================= --}}
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    @if($user->profile_image)
                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="" class="rounded-circle mb-3"
                             style="width:110px;height:110px;object-fit:cover;">
                    @else
                        <div class="rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center"
                             style="width:110px;height:110px;background:#134AA4;color:#fff;font-size:40px;font-weight:700;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <div class="mb-2">
                        <span class="badge bg-info text-dark">{{ $user->role ? ucfirst($user->role->name) : 'No role' }}</span>
                        @if($user->super_user)
                            <span class="badge bg-success">Super Admin</span>
                        @endif
                    </div>
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Email</span><span class="text-break ms-2">{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Phone</span><span>{{ $user->phone ?: '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Shop</span><span>{{ $user->shop->name ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Verified</span>
                        <span>{{ $user->verified ? 'Yes' . ($user->verified_at ? ' (' . $user->verified_at->format('Y-m-d') . ')' : '') : 'No' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Registered</span><span>{{ $user->created_at?->format('Y-m-d H:i') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Last updated</span><span>{{ $user->updated_at?->format('Y-m-d H:i') }}</span>
                    </li>
                </ul>

                @unless($user->id === auth()->id())
                    <div class="card-body">
                        <form action="{{ route('users.manage.destroy', $user->id) }}" method="POST"
                              onsubmit="return confirm('Delete {{ addslashes($user->name) }}? They will no longer be able to log in.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete User
                            </button>
                        </form>
                    </div>
                @endunless
            </div>
        </div>

        {{-- ================= EDIT ================= --}}
        <div class="col-lg-8" id="edit">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <i class="bi bi-pencil"></i> Edit Details
                </div>
                <div class="card-body">
                    <form action="{{ route('users.manage.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role_id" class="form-control" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (int) old('role_id', $user->role_id) === $role->id ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">JPG, PNG or WEBP, up to 3 MB. Leave empty to keep the current photo.</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Set New Password <small class="text-muted fw-normal">(leave empty to keep the current password)</small></h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
