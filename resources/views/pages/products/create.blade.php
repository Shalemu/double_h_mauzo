@extends('main')

@section('title', 'Add Product')

@section('content')
@include('pages.products._create_styles')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <p class="text-muted mb-0">
                Fill in the details below to register a new product
                @if($targetShop)
                    in <strong>{{ $targetShop->name }}</strong>{{ $targetShop->isStore() ? ' (Store)' : '' }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-list-ul"></i> Product List
            </a>
        </div>
    </div>

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

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="addProductForm">
        @csrf

        <!-- Classification -->
        <div class="card product-form-section accent-primary mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-primary"><i class="bi bi-tags"></i></div>
                <div>
                    <p class="product-form-section-title">Classification</p>
                    <p class="product-form-section-subtitle">Where it's kept, its category and brand</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    @if($shops->isNotEmpty())
                        <div class="col-md-4">
                            <label for="shop_id" class="form-label">Shop / Store <span class="text-danger">*</span></label>
                            <select name="shop_id" id="shop_id" class="form-select" required>
                                <option value="" disabled {{ old('shop_id') ? '' : 'selected' }}>-- select shop or store --</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="{{ $shops->isNotEmpty() ? 'col-md-4' : 'col-md-6' }}">
                        <label for="category_id" class="form-label">Item Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">-- select category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ $shops->isNotEmpty() ? 'col-md-4' : 'col-md-6' }}">
                        <label for="brand" class="form-label">Brand</label>
                        <select name="brand" id="brand" class="form-select">
                            <option value="">-- select brand --</option>
                            @foreach($brands as $brandName)
                                <option value="{{ $brandName }}" {{ old('brand') === $brandName ? 'selected' : '' }}>{{ $brandName }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Not listed? <a href="{{ route('brands.index') }}" target="_blank" rel="noopener">Add a brand</a>, then reload this page.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Information -->
        <div class="card product-form-section accent-success mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-success"><i class="bi bi-box-seam"></i></div>
                <div>
                    <p class="product-form-section-title">Product Information</p>
                    <p class="product-form-section-subtitle">Name, barcode, unit, size, colour and photo</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" autofocus required>
                    </div>
                    <div class="col-md-6">
                        <label for="barcode" class="form-label">Barcode <span class="text-muted">(optional)</span></label>
                        <input type="text" name="barcode" id="barcode" class="form-control" placeholder="Scan barcode" value="{{ old('barcode') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="unit_id" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-select" required>
                            <option value="" disabled {{ old('unit_id') ? '' : 'selected' }}>-- select unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->short_name }}){{ $unit->baseUnit ? ' — ' . $unit->multiplier . ' ' . $unit->baseUnit->short_name : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="size" class="form-label">Size</label>
                        <input type="text" name="size" id="size" class="form-control" value="{{ old('size') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="color" class="form-label">Colour</label>
                        <input type="text" name="color" id="color" class="form-control" value="{{ old('color') }}">
                    </div>
                    <div class="col-12">
                        <label for="image" class="form-label">Product Image <span class="text-muted">(optional)</span></label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        <img id="imagePreview" class="product-image-preview" src="#" alt="Selected product image preview">
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="card product-form-section accent-warning mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-warning"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <p class="product-form-section-title">Pricing</p>
                    <p class="product-form-section-subtitle">What you pay for it and what you sell it for (TZS)</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="purchase_price" class="form-label">Retail Purchasing Price <span class="text-danger">*</span></label>
                        <input type="number" name="purchase_price" id="purchase_price" class="form-control" min="0" step="any" value="{{ old('purchase_price') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="selling_price" class="form-label">Retail Selling Price</label>
                        <input type="number" name="selling_price" id="selling_price" class="form-control" min="0" step="any" value="{{ old('selling_price') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="wholesale_price" class="form-label">Wholesale Selling Price</label>
                        <input type="number" name="wholesale_price" id="wholesale_price" class="form-control" min="0" step="any" value="{{ old('wholesale_price') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock -->
        <div class="card product-form-section accent-info mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-info"><i class="bi bi-boxes"></i></div>
                <div>
                    <p class="product-form-section-title">Stock</p>
                    <p class="product-form-section-subtitle">Opening quantity, stock alert level, expiry and invoice</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" value="{{ old('quantity') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="min_quantity" class="form-label">Stock Alert Level</label>
                        <input type="number" name="min_quantity" id="min_quantity" class="form-control" min="0" value="{{ old('min_quantity') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="expire_date" class="form-label">Expire Date</label>
                        <input type="date" name="expire_date" id="expire_date" class="form-control" value="{{ old('expire_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="invoice_number" class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control" value="{{ old('invoice_number') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end flex-wrap gap-2 mb-5">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" name="add_another" value="1" class="btn btn-outline-primary px-4">
                <i class="bi bi-plus-circle me-1"></i> Save &amp; Add Another
            </button>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle me-1"></i> Save Product
            </button>
        </div>
    </form>
</div>

@include('pages.products._create_scripts')
@endsection
