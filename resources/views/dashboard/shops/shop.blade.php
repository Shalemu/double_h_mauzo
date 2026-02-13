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
    <div class="container-fluid">
        <div class="row g-4" style="padding-left:30px; padding-right:30px;">
            <div class="col-xl-10 mx-auto">
                <div class="cat__core__widget p-3 h-100" style="background:#fff;">

                    <!-- Top Bar: Search + Export | Back + Add Shop -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

                        <!-- Left side -->
                        <div class="d-flex align-items-center mb-2">
                            <div style="margin-right:8px;">
                                <input type="text" id="shopSearch" class="form-control form-control-sm" placeholder="Search shops...">
                            </div>
                            <div style="margin-right:8px;">
                                <button class="btn btn-sm btn-success">Export Excel</button>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-danger">Export PDF</button>
                            </div>
                        </div>

                        <!-- Right side -->
                        <div class="d-flex align-items-center mb-2">
                            <div style="margin-right:8px;">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Back</a>
                            </div>
                            <div>
                                <button class="btn btn-primary btn-sm" id="toggleAddShop">Add Shop</button>
                            </div>
                        </div>

                    </div>

<!-- Add Shop Form (hidden by default) -->
<div id="addShopForm" class="mb-3" 
     @if(!(session('success') || $errors->any())) style="display:none;" @endif>

    <!-- Success / Error Messages -->
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

    <!-- Add Shop Form -->
    <form method="POST" action="{{ route('shops.store') }}">
        @csrf
        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="Shop Name" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="location" class="form-control" placeholder="Location" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-sm">Save Shop</button>
    </form>
</div>


                    <!-- Shop Tabs (can be empty for now if you don't have dynamic tabs) -->
                    <ul class="nav nav-tabs mb-3 justify-content-center" id="shopTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="shop1-tab" data-bs-toggle="tab" href="#shop1" role="tab">Shops</a>
                        </li>
                    </ul>

                    <!-- Tab Content (Tables) -->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="shop1" role="tabpanel">
<table class="table table-bordered text-center w-75 mx-auto text-uppercase shop-table">
    <thead class="table-warning">
        <tr>
            <th>Name</th>
            <th>Employee</th>
            <th>Total Wages (TZS)</th>
            <th>Capital (TZS)</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        @if($shops->count() > 0)
            @foreach($shops as $shop)
                <tr>
                    <td>
                <a href="{{ route('dashboard.shop.show', ['shop' => $shop->id]) }}">
                    {{ $shop->name }}
                </a>

                    </td>
                    <td>{{ $shop->total_employees }}</td>
                    <td>{{ number_format($shop->total_wages) }}</td>
                    <td>{{ number_format($shop->calculated_capital, 2) }}</td>
                    <td>{{ $shop->location }}</td>
                </tr>

                @php
                    $totalCapital += $shop->calculated_capital;
                @endphp
            @endforeach
        @else
            <tr>
                <td colspan="5">No shops found.</td>
            </tr>
        @endif

        <tr class="table-success">
            <td colspan="3"><strong>Total Capital</strong></td>
            <td><strong>{{ number_format($totalCapital, 2) }}</strong></td>
            <td></td>
        </tr>
    </tbody>
</table>



                        </div>
                    </div> <!-- tab-content -->

                </div> <!-- widget -->
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
