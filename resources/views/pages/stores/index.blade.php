@extends('main')

@section('title', 'Stores')

@section('content')

@include('pages.stores._index_styles')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="border rounded-4 shadow-sm bg-white p-4" style="border: 1px solid #dee2e6 !important;">

                <!-- Top Toolbar -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-building me-2"></i>Stores</h5>
                        <small class="text-muted">Stores hold stock only — they don't sell at the POS. Send stock to shops with Stock Transfers, or out with distributors from Distribution.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <input type="text" id="storeSearch" class="form-control" placeholder="Search stores..." style="width: 240px; border-radius: 10px;" aria-label="Search stores">
                        <button class="btn btn-primary px-3" id="toggleAddStore">
                            <i class="bi bi-plus-circle"></i> Add Store
                        </button>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
                @endif

                @php
                    $activeStore = session('active_shop_id') ? $stores->firstWhere('id', (int) session('active_shop_id')) : null;
                @endphp

                <!-- Active Store Switcher: Product Management and Purchases follow it -->
                <div class="alert {{ $activeStore ? 'active-store-banner' : 'alert-secondary' }} d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-3">
                    <div>
                        @if($activeStore)
                            <i class="bi bi-building me-1"></i> Working in <strong>{{ $activeStore->name }}</strong> — Product Management and Purchases now show this store's stock.
                        @else
                            <i class="bi bi-box-seam me-1"></i> Pick a store to manage its products or buy stock into it.
                        @endif
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.activeShop.set') }}" class="d-flex gap-2">
                            @csrf
                            <select name="shop_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:200px;" aria-label="Work in store">
                                <option value="">-- Work in store --</option>
                                @foreach($stores as $s)
                                    <option value="{{ $s->id }}" {{ $activeStore && $activeStore->id === $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}{{ $s->isSuspended() ? ' (Suspended)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        @if($activeStore)
                            <form method="POST" action="{{ route('admin.activeShop.clear') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn store-view-all-btn">Stop working in store</button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Add Store Form -->
                <div id="addStoreForm" class="border rounded-3 p-4 mb-4 bg-light" @if(!$errors->any()) style="display:none;" @endif>
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('stores.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control" placeholder="Store Name" value="{{ old('name') }}" required aria-label="Store name">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="location" class="form-control" placeholder="Location" value="{{ old('location') }}" required aria-label="Location">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success px-4">Save Store</button>
                        </div>
                    </form>
                </div>

                <!-- Stores Table -->
                <div class="border rounded-3 p-2">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center mb-0 store-table w-100">
                            <thead class="table-info">
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Products</th>
                                    <th>Stock Value (TZS)</th>
                                    <th>Staff</th>
                                    <th>Distributors Out</th>
                                    <th>Owed by Distributors (TZS)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalStock = 0; $totalOwed = 0; @endphp

                                @forelse($stores as $store)
                                    @php $stockValue = $store->stock_value; @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('stores.show', $store) }}" class="text-decoration-none fw-semibold">{{ $store->name }}</a>
                                        </td>
                                        <td>{{ $store->location }}</td>
                                        <td>
                                            <a href="{{ route('stores.show', $store) }}" class="text-decoration-none">{{ number_format($store->products_count) }}</a>
                                        </td>
                                        <td>{{ number_format($stockValue, 2) }}</td>
                                        <td>{{ $store->staff_count }}</td>
                                        <td>
                                            <a href="{{ route('distributions.index', ['store' => $store->id, 'status' => 'out']) }}" class="text-decoration-none">
                                                {{ $store->distributions_out_count }}
                                            </a>
                                        </td>
                                        <td class="{{ $store->distributor_balance > 0 ? 'text-danger fw-semibold' : '' }}">{{ number_format($store->distributor_balance ?? 0, 2) }}</td>
                                        <td>
                                            @if($store->isSuspended())
                                                <span class="badge bg-secondary">Suspended</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('stores.show', $store) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStoreModal{{ $store->id }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

                                                <form method="POST" action="{{ route('stores.toggleStatus', $store->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $store->isSuspended() ? 'btn-outline-success' : 'btn-outline-secondary' }}">
                                                        @if($store->isSuspended())
                                                            <i class="bi bi-play-circle"></i> Activate
                                                        @else
                                                            <i class="bi bi-pause-circle"></i> Suspend
                                                        @endif
                                                    </button>
                                                </form>

                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStoreModal{{ $store->id }}">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>

                                            <!-- Edit Store Modal -->
                                            <div class="modal fade" id="editStoreModal{{ $store->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form class="modal-content" method="POST" action="{{ route('stores.update', $store->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Store</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label">Store Name</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $store->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Location</label>
                                                                <input type="text" name="location" class="form-control" value="{{ $store->location }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Delete Store Modal -->
                                            <div class="modal fade" id="deleteStoreModal{{ $store->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form class="modal-content" method="POST" action="{{ route('stores.destroy', $store->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Delete Store</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <p class="mb-2">Delete <strong>"{{ $store->name }}"</strong>?</p>
                                                            <p class="text-muted mb-0">This will permanently delete this store and all of its products, distributions and other related data. This cannot be undone.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete Store</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @php
                                        $totalStock += $stockValue;
                                        $totalOwed += $store->distributor_balance ?? 0;
                                    @endphp
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-muted py-4">No stores yet. Click <strong>Add Store</strong> to create one.</td>
                                    </tr>
                                @endforelse

                                @if($stores->isNotEmpty())
                                    <tr class="table-success fw-bold">
                                        <td colspan="3">Total</td>
                                        <td>{{ number_format($totalStock, 2) }}</td>
                                        <td colspan="2"></td>
                                        <td>{{ number_format($totalOwed, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('pages.stores._index_scripts')
@endsection
