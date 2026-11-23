@extends('main')

@section('title', 'Add Product')

@section('content')
@include('pages.products._create_styles')
@php
    // Products already on the list (back after a validation error). Photos have to be picked again.
    $oldProducts = array_values(old('products', []));
@endphp

<style>
#productList td, #productList th { vertical-align: middle; }
#productList tr.just-added { animation: apFlash 1.2s ease-out; }
@keyframes apFlash { from { background: rgba(25,135,84,.18); } to { background: transparent; } }
#productEntry.editing .product-form-section { border-color: #0d6efd !important; }
</style>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <p class="text-muted mb-0">
            Fill in a product and click <strong>Add to list</strong>. Add as many as you need, then <strong>Save all products</strong>
            @if($targetShop)
                into <strong>{{ $targetShop->name }}</strong>{{ $targetShop->isStore() ? ' (Store)' : '' }}
            @endif.
        </p>
        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-list-ul"></i> Product List</a>
        </div>
    </div>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="addProductForm" novalidate>
        @csrf

        {{-- Shared by every product on the list --}}
        <div class="card product-form-section accent-primary mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-primary"><i class="bi bi-shop"></i></div>
                <div>
                    <p class="product-form-section-title">Where they go</p>
                    <p class="product-form-section-subtitle">Applies to every product on the list</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    @if($shops->isNotEmpty())
                        <div class="col-md-6">
                            <label for="shop_id" class="form-label">Shop / Store <span class="text-danger">*</span></label>
                            <select name="shop_id" id="shop_id" class="form-select" required>
                                <option value="" disabled {{ old('shop_id') ? '' : 'selected' }}>-- select shop or store --</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($targetShop)
                        <div class="col-md-6">
                            <label class="form-label">Shop / Store</label>
                            <div class="form-control bg-light">{{ $targetShop->name }}{{ $targetShop->isStore() ? ' (Store)' : '' }}</div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label for="invoice_number" class="form-label">Invoice Number <span class="text-muted">(optional)</span></label>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control" maxlength="100" value="{{ old('invoice_number') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- One product at a time. No name attributes: only the list below is submitted. --}}
        <div id="productEntry">
            <div class="d-flex align-items-center gap-2 mb-2">
                <h6 class="mb-0" id="entryTitle"><i class="bi bi-box-seam me-1"></i> Product details</h6>
            </div>

            <!-- Classification + information -->
            <div class="card product-form-section accent-success mb-4">
                <div class="product-form-section-header">
                    <div class="product-form-section-icon icon-success"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <p class="product-form-section-title">Product Information</p>
                        <p class="product-form-section-subtitle">Category, brand, name, barcode, unit, size, colour and photo</p>
                    </div>
                </div>
                <div class="product-form-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="p_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" id="p_name" class="form-control" maxlength="255" autofocus>
                        </div>
                        <div class="col-md-6">
                            <label for="p_barcode" class="form-label">Barcode <span class="text-muted">(optional)</span></label>
                            <input type="text" id="p_barcode" class="form-control" maxlength="100" placeholder="Scan barcode">
                        </div>
                        <div class="col-md-4">
                            <label for="p_category" class="form-label">Item Category</label>
                            <select id="p_category" class="form-select">
                                <option value="">-- select category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="p_brand" class="form-label">Brand</label>
                            <select id="p_brand" class="form-select">
                                <option value="">-- select brand --</option>
                                @foreach($brands as $brandName)
                                    <option value="{{ $brandName }}">{{ $brandName }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Not listed? <a href="{{ route('brands.index') }}" target="_blank" rel="noopener">Add a brand</a>, then reload this page.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="p_unit" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                            <select id="p_unit" class="form-select">
                                <option value="">-- select unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }}){{ $unit->baseUnit ? ' — ' . $unit->multiplier . ' ' . $unit->baseUnit->short_name : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="p_size" class="form-label">Size</label>
                            <input type="text" id="p_size" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-3">
                            <label for="p_color" class="form-label">Colour</label>
                            <input type="text" id="p_color" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-6" id="p_imageBox">
                            <label for="p_image" class="form-label">Product Image <span class="text-muted">(optional)</span></label>
                            <input type="file" id="p_image" class="form-control" accept="image/*">
                            <img id="imagePreview" class="product-image-preview" src="#" alt="Selected product image preview">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing + stock -->
            <div class="card product-form-section accent-warning mb-3">
                <div class="product-form-section-header">
                    <div class="product-form-section-icon icon-warning"><i class="bi bi-cash-coin"></i></div>
                    <div>
                        <p class="product-form-section-title">Pricing &amp; Stock</p>
                        <p class="product-form-section-subtitle">Prices (TZS), opening quantity, alert level and expiry</p>
                    </div>
                </div>
                <div class="product-form-section-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="p_purchase" class="form-label">Retail Purchasing Price <span class="text-danger">*</span></label>
                            <input type="number" id="p_purchase" class="form-control" min="0" step="any">
                        </div>
                        <div class="col-md-4">
                            <label for="p_selling" class="form-label">Retail Selling Price</label>
                            <input type="number" id="p_selling" class="form-control" min="0" step="any">
                        </div>
                        <div class="col-md-4">
                            <label for="p_wholesale" class="form-label">Wholesale Selling Price</label>
                            <input type="number" id="p_wholesale" class="form-control" min="0" step="any">
                        </div>
                        <div class="col-md-4">
                            <label for="p_quantity" class="form-label">Quantity</label>
                            <input type="number" id="p_quantity" class="form-control" min="0" step="1">
                        </div>
                        <div class="col-md-4">
                            <label for="p_min" class="form-label">Stock Alert Level</label>
                            <input type="number" id="p_min" class="form-control" min="0" step="1">
                        </div>
                        <div class="col-md-4">
                            <label for="p_expiry" class="form-label">Expire Date</label>
                            <input type="date" id="p_expiry" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-danger small mb-2" id="entryError" role="alert" hidden></div>
            <div class="d-flex justify-content-end gap-2 mb-4">
                <button type="button" class="btn btn-light" id="cancelEdit" hidden>Cancel edit</button>
                <button type="button" class="btn btn-primary px-4" id="addToList"><i class="bi bi-plus-lg me-1"></i><span>Add to list</span></button>
            </div>
        </div>

        {{-- Products added --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Products to save <span class="badge bg-secondary" id="productCount">0</span></h6>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="productList">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width:40px">#</th>
                            <th class="text-start">Product</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-end">Buy</th>
                            <th class="text-end">Retail</th>
                            <th class="text-end">Wholesale</th>
                            <th>Qty</th>
                            <th>Alert</th>
                            <th>Expiry</th>
                            <th style="width:110px">Action</th>
                        </tr>
                    </thead>
                    <tbody id="productRows"></tbody>
                    <tfoot>
                        <tr id="emptyRow">
                            <td colspan="11" class="text-center text-muted py-4">No products yet. Fill in the details above and click <strong>Add to list</strong>.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end flex-wrap gap-2 mb-5">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" class="btn btn-success px-4" id="saveAll">
                <i class="bi bi-check-circle me-1"></i> Save all products
            </button>
        </div>
    </form>
</div>

<script>window.addProductOld = @json($oldProducts);</script>
@include('pages.products._create_scripts')
@endsection
