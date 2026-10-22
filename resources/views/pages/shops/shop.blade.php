@php
$shops = $shops ?? collect();
$totalCapital = 0;
@endphp


@extends('main')

@section('title', 'Business Management')

@section('content')

@include('pages.shops._shop_styles')

<div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                
                <!-- Premium Main Container -->
                <div class="border rounded-4 shadow-sm bg-white p-4"
                     style="width: 100%; max-width: none; margin: auto; border: 1px solid #dee2e6 !important;">

                    <!-- Top Toolbar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                        
                        <!-- Left Section -->
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <input 
                                type="text" 
                                id="shopSearch" 
                                class="form-control"
                                placeholder="Search shops..."
                                style="width: 280px; border-radius: 10px;"
                            >

                            <button class="btn btn-success px-3">
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </button>

                            <button class="btn btn-danger px-3">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </button>
                        </div>

                        <!-- Right Section -->
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-3">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>

                            <button class="btn btn-primary px-3" id="toggleAddShop">
                                <i class="bi bi-plus-circle"></i> Add Shop
                            </button>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success rounded-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger rounded-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    @php
                        $activeShop = session('active_shop_id') ? $shops->firstWhere('id', (int) session('active_shop_id')) : null;
                    @endphp

                    <!-- Active Shop Switcher -->
                    <div class="alert {{ $activeShop ? 'active-shop-banner' : 'alert-secondary' }} d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-3">
                        <div>
                            @if($activeShop)
                                <i class="bi bi-shop me-1"></i> Working in <strong>{{ $activeShop->name }}</strong> — other pages (e.g. Manage Products) will default to this shop.
                            @else
                                <i class="bi bi-globe me-1"></i> Viewing <strong>all shops</strong> (no active shop selected).
                            @endif
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <form method="POST" action="{{ route('admin.activeShop.set') }}" class="d-flex gap-2">
                                @csrf
                                <select name="shop_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:200px;">
                                    <option value="">-- Set active shop --</option>
                                    @foreach($shops as $s)
                                        <option value="{{ $s->id }}" {{ $activeShop && $activeShop->id === $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}{{ $s->isSuspended() ? ' (Suspended)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            @if($activeShop)
                                <form method="POST" action="{{ route('admin.activeShop.clear') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary shop-view-all-btn">View all shops</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Add Shop Form -->
                    <div id="addShopForm" class="border rounded-3 p-4 mb-4 bg-light"
                         @if(!$errors->any()) style="display:none;" @endif>

                        @if($errors->any())
                            <div class="alert alert-danger rounded-3">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('shops.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="name" class="form-control"
                                           placeholder="Shop Name" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-md-4">
                                    <input type="text" name="location" class="form-control"
                                           placeholder="Location" value="{{ old('location') }}" required>
                                </div>

                                <div class="col-md-4">
                                    <input type="number" name="capital" class="form-control"
                                           placeholder="Initial Capital" value="{{ old('capital') }}">
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success px-4">
                                    Save Shop
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Table Container -->
                    <div class="border rounded-3 p-2 shop-table-container">
                        <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center mb-0 shop-table w-100">
                            <thead class="table-warning">
                                <tr>
                                    <th>Name</th>
                                    <th>Employee</th>
                                    <th>Total Wages (TZS)</th>
                                    <th>Stock Value (TZS)</th>
                                    <th>Capital (TZS)</th>
                                    <th>Real Capital (TZS)</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalStock = 0; $totalCapital = 0; @endphp

                                @forelse($shops as $shop)
                                    <tr>
                                        <td>
                                            <a href="{{ route('dashboard.shop.show', ['shop' => $shop->id]) }}"
                                               class="text-decoration-none fw-semibold">
                                                {{ $shop->name }}
                                            </a>
                                        </td>
                                        <td>{{ $shop->total_employees }}</td>
                                        <td>{{ number_format($shop->total_wages) }}</td>
                                        <td>{{ number_format($shop->stock_value, 2) }}</td>
                                        <td>{{ number_format($shop->capital, 2) }}</td>
                                        <td>{{ number_format($shop->realCapital, 2) }}</td>
                                        <td>{{ $shop->location }}</td>
                                        <td>
                                            @if($shop->isSuspended())
                                                <span class="badge bg-secondary">Suspended</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editShopModal{{ $shop->id }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

                                                <form method="POST" action="{{ route('shops.toggleStatus', $shop->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $shop->isSuspended() ? 'btn-outline-success' : 'btn-outline-secondary' }}">
                                                        @if($shop->isSuspended())
                                                            <i class="bi bi-play-circle"></i> Activate
                                                        @else
                                                            <i class="bi bi-pause-circle"></i> Suspend
                                                        @endif
                                                    </button>
                                                </form>

                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal" data-bs-target="#deleteShopModal{{ $shop->id }}">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>

                                            <!-- Edit Shop Modal -->
                                            <div class="modal fade" id="editShopModal{{ $shop->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('shops.update', $shop->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Shop</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Shop Name</label>
                                                                    <input type="text" name="name" class="form-control"
                                                                           value="{{ $shop->name }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Location</label>
                                                                    <input type="text" name="location" class="form-control"
                                                                           value="{{ $shop->location }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Capital</label>
                                                                    <input type="number" name="capital" class="form-control"
                                                                           value="{{ $shop->capital }}">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Shop Modal -->
                                            <div class="modal fade" id="deleteShopModal{{ $shop->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('shops.destroy', $shop->id) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-danger">
                                                                    <i class="bi bi-exclamation-triangle-fill"></i> Delete Shop
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <p class="mb-2">
                                                                    Delete <strong>"{{ $shop->name }}"</strong>?
                                                                </p>
                                                                <p class="text-muted mb-0">
                                                                    This will permanently delete this shop and all of its products,
                                                                    staff, sales, expenses and other related data. This cannot be undone.
                                                                </p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="bi bi-trash"></i> Delete Shop
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    @php
                                        $totalStock += $shop->stock_value;
                                        $totalCapital += $shop->realCapital;
                                    @endphp
                                @empty
                                    <tr>
                                        <td colspan="9">No shops found.</td>
                                    </tr>
                                @endforelse

                                <tr class="table-success fw-bold">
                                    <td colspan="3">Total</td>
                                    <td>{{ number_format($totalStock, 2) }}</td>
                                    <td></td>
                                    <td>{{ number_format($totalCapital, 2) }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</div>


<!-- JS: Toggle form and live search -->
@include('pages.shops._shop_scripts')

</div>

@endsection
