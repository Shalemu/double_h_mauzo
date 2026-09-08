@php
$shops = $shops ?? collect();
@endphp

@extends('main')

@section('title', 'User Management')

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

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
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

                    <!-- ================= PENDING APPROVALS ================= -->
                    <div class="cat__core__widget p-3 mb-4" style="background:#fff;">
                        <h5 class="mb-3">Pending Approvals</h5>

                        <table class="table table-bordered text-center">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Registered</th>
                                    <th>Assign Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingUsers as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone }}</td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <form action="{{ route('users.manage.approve', $user->id) }}" method="POST" class="d-flex gap-2 justify-content-center">
                                                @csrf
                                                @method('PUT')
                                                <select name="role_id" class="form-control form-control-sm" style="max-width:160px;" required>
                                                    <option value="">Select Role</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{{ route('users.manage.reject', $user->id) }}" method="POST" onsubmit="return confirm('Reject this registration?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted">No pending registrations.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= MANAGE EXISTING USERS ================= -->
                    <div class="cat__core__widget p-3" style="background:#fff;">
                        <h5 class="mb-3">Manage Existing Users</h5>

                        <table class="table table-bordered text-center">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Super Admin</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($approvedUsers as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <form action="{{ route('users.manage.role', $user->id) }}" method="POST" class="d-flex gap-2 justify-content-center">
                                                @csrf
                                                @method('PUT')
                                                <select name="role_id" class="form-control form-control-sm" style="max-width:160px;" required>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                            {{ ucfirst($role->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-info">Save</button>
                                            </form>
                                        </td>
                                        <td>
                                            @if($user->id === auth()->id())
                                                <span class="badge {{ $user->super_user ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $user->super_user ? 'Yes' : 'No' }}
                                                </span>
                                            @else
                                                <form action="{{ route('users.manage.superUser', $user->id) }}" method="POST" onsubmit="return confirm('Change Super Admin status for this user?')">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm {{ $user->super_user ? 'btn-success' : 'btn-outline-secondary' }}">
                                                        {{ $user->super_user ? 'Yes — click to revoke' : 'No — click to grant' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">No approved users yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- REQUIRED BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection
