<!-- Add Purchase Form Buttons -->
<div class="mb-3 d-flex gap-2">
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-circle"></i> New Item
    </button>
    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="bi bi-person-plus"></i> Add Supplier
    </button>
</div>

<section class="card mb-5 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
        <h5 class="mb-0">Add New Purchase</h5>
        <button type="button" class="btn btn-danger btn-sm" id="cancel-add-purchase">Cancel</button>
    </div>

    <div class="card-body">

        <!-- Validation Errors -->
        @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the errors below:<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {!! Form::open(['route' => 'purchases.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="row g-3">

            <!-- Shop -->
            <div class="col-md-6">
                <label class="form-label">Shop <span class="text-danger">*</span></label>
                <select id="shopSelect" class="form-select form-select-lg" name="shop_id" required>
                    <option value="">Select Shop</option>
                    @foreach($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Supplier -->
            <div class="col-md-6">
                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                <select id="supplierSelect" class="form-select form-select-lg" name="supplier_id" required>
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Product -->
            <div class="col-md-6">
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select id="productSelect" class="form-select form-select-lg" name="product_id" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category -->
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select id="categorySelect" class="form-select form-select-lg" name="category_id">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Unit -->
            <div class="col-md-6">
                <label class="form-label">Unit <span class="text-danger">*</span></label>
                <select id="unitSelect" class="form-select form-select-lg" name="unit_id" required>
                    <option value="">Select Unit</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Quantity -->
            <div class="col-md-6">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" name="quantity" class="form-control form-control-lg" min="1" placeholder="Enter quantity" required>
            </div>

            <!-- Purchase Price -->
            <div class="col-md-6">
                <label class="form-label">Purchase Price (Tsh) <span class="text-danger">*</span></label>
                <input type="number" name="purchase_price" class="form-control form-control-lg" min="0" step="0.01" placeholder="Enter purchase price" required>
            </div>

            <!-- Selling Price -->
            <div class="col-md-6">
                <label class="form-label">Selling Price (Tsh) <span class="text-danger">*</span></label>
                <input type="number" name="selling_price" class="form-control form-control-lg" min="0" step="0.01" placeholder="Enter selling price" required>
            </div>

            <!-- Invoice Number -->
            <div class="col-md-6">
                <label class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control form-control-lg" placeholder="Invoice number">
            </div>

            <!-- Expire Date -->
            <div class="col-md-6">
                <label class="form-label">Expire Date</label>
                <input type="date" name="expire_date" class="form-control form-control-lg">
            </div>

            <!-- Barcode -->
            <div class="col-md-6">
                <label class="form-label">Barcode</label>
                <input type="text" name="barcode" class="form-control form-control-lg" placeholder="Barcode">
            </div>

            <!-- Image Upload -->
            <div class="col-md-12">
                <label class="form-label">Product Image (Optional)</label>
                <input type="file" name="image" class="form-control form-control-lg" accept="image/*" onchange="previewImage(event)">
                <img id="imagePreview" src="#" alt="Preview" style="display:none; max-height:120px; margin-top:10px; border-radius:5px; border:1px solid #ccc;">
            </div>

        </div>

        <div class="d-flex justify-content-end mt-4 gap-2">
            <button type="submit" class="btn btn-success">Submit</button>
            <button type="reset" class="btn btn-warning">Reset</button>
            <button type="button" class="btn btn-secondary" id="cancel-add-purchase">Cancel</button>
        </div>
        {!! Form::close() !!}
    </div>
</section>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierLabel" aria-hidden="true" style="margin-top: 150px;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSupplierLabel">Add New Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {!! Form::open(['route' => 'suppliers.store', 'method' => 'POST']) !!}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg" placeholder="Enter supplier name" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control form-control-lg" placeholder="Enter phone">
            </div>
            <div class="col-md-12">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter email">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control form-control-lg" placeholder="Address"></textarea>
            </div>
        </div>
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-success">Add Supplier</button>
        </div>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true" style="margin-top: 150px;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductLabel">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {!! Form::open(['route' => 'products.store', 'method' => 'POST']) !!}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg" placeholder="Enter product name" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">SKU Code</label>
                <input type="text" name="sku_code" class="form-control form-control-lg" placeholder="SKU code">
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select class="form-select form-select-lg" name="category_id">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-success">Add Product</button>
        </div>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script>
$(document).ready(function() {
    $('#shopSelect, #supplierSelect, #productSelect, #categorySelect, #unitSelect').select2({
        placeholder: 'Select an option',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0
    });
});

// Image preview
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = function(e){
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
}
</script>
