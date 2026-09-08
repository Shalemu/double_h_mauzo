@php
$shops = $shops ?? collect();
$totalCapital = 0;
@endphp


@section('title', 'Dashboard')
@include('main')
@include('components/breadcrumb')
@include('components/mainmenu')


<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="cat__content">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                
                <!-- Premium Main Container -->
                <div class="border rounded-4 shadow-sm bg-white p-4"
                     style="max-width: 1500px; margin: auto; border: 1px solid #dee2e6 !important;">

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
                                           placeholder="Shop Name" required>
                                </div>

                                <div class="col-md-4">
                                    <input type="text" name="location" class="form-control"
                                           placeholder="Location" required>
                                </div>

                                <div class="col-md-4">
                                    <input type="number" name="capital" class="form-control"
                                           placeholder="Initial Capital">
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
                    <div class="border rounded-3 p-3">
                        <table class="table table-hover table-bordered align-middle text-center mb-0 shop-table">
                            <thead class="table-warning">
                                <tr>
                                    <th>Name</th>
                                    <th>Employee</th>
                                    <th>Total Wages (TZS)</th>
                                    <th>Stock Value (TZS)</th>
                                    <th>Real Capital (TZS)</th>
                                    <th>Location</th>
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
                                        <td>{{ number_format($shop->realCapital, 2) }}</td>
                                        <td>{{ $shop->location }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editShopModal{{ $shop->id }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

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
                                        <td colspan="7">No shops found.</td>
                                    </tr>
                                @endforelse

                                <tr class="table-success fw-bold">
                                    <td colspan="3">Total</td>
                                    <td>{{ number_format($totalStock, 2) }}</td>
                                    <td>{{ number_format($totalCapital, 2) }}</td>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Add Shop form
    const toggleBtn = document.getElementById('toggleAddShop');
    const addForm = document.getElementById('addShopForm');

    toggleBtn.addEventListener('click', function() {
        if(addForm.style.display === 'none' || addForm.style.display === '') {
            addForm.style.display = 'block';
        } else {
            addForm.style.display = 'none';
        }
    });

    // Live search for shop name
    const searchInput = document.getElementById('shopSearch');
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toUpperCase();
        document.querySelectorAll('.shop-table tbody tr').forEach(tr => {
            if(tr.classList.contains('table-success')) return;
            const nameCell = tr.querySelector('td:first-child');
            if(nameCell && nameCell.textContent.toUpperCase().indexOf(filter) > -1) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    });
});
</script>
