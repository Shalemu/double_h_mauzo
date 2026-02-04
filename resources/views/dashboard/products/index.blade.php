@section('title', 'Manage Product')
@php
    $products   = $products   ?? collect();
    $categories = $categories ?? collect();
    $units      = $units      ?? collect();
@endphp

<div class="cat__content">

    <div class="d-flex justify-content-end gap-1 mt-3 flex-wrap" style="margin-top: 60px; margin-right: 80px;">
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#uploadExcelModal">
          <i class="fa fa-upload"></i> Upload Excel
        </button>
    </div>

    <!-- Upload Excel Modal -->
<div class="modal fade" id="uploadExcelModal" tabindex="-1" aria-labelledby="uploadExcelModalLabel" aria-hidden="true" style="margin-top: 150px;">
  <div class="modal-dialog">
    <form action="{{ route('products.import.excel') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadExcelModalLabel">Upload Products Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You can <a href="{{ route('products.download.template') }}">download the sample Excel format</a> to fill in product data.</p>
                <div class="mb-3">
                    <label for="excel_file" class="form-label">Choose Excel file</label>
                    <input type="file" name="excel_file" id="excel_file" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Upload</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </form>
  </div>
</div>

        
            
             
        

    <!-- START: ecommerce/product-list -->
    <section class="card" style="max-width: 1300px; margin-top: 40px; margin-left:120px;">
        <!-- Card Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="cat__core__title"><strong>Product List</strong></span>
            
          <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fa fa-plus"></i> Add Product
        </a>
        </div>

        

        <!-- Card Body -->
        <div class="card-body">
           
     <div class="d-flex gap-2 mb-2">
               <a href="{{ route('products.export.excel') }}" class="btn btn-success btn-sm">
    <i class="fa fa-file-excel-o"></i> Export Excel
      </a>

      <a href="{{ route('products.export.pdf') }}" class="btn btn-danger btn-sm">
          <i class="fa fa-file-pdf-o"></i> Export PDF
      </a>

            </div>
            <!-- Table -->
            <table class="table table-hover table-bordered text-center" id="productTable" style="width: 100%;">
                <thead class="table-warning">
                    <tr>
                        <th>Img</th>
                        <th>Name</th>
                        <th>Available Quantity</th>
                        <th>Unit</th>
                        <th>Purchase Price</th>
                        <th>Sale Price</th>
                        <th>Expire Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
              <tbody>
    @foreach($products as $product)
    <tr>
        <td>
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" width="50">
            @else
                <img src="https://via.placeholder.com/50" alt="No Image" width="50">
            @endif
        </td>
        <td>{{ $product->name }}</td>
        <td>{{ $product->quantity ?? 0 }}</td>
        <td>{{ $product->unit ? $product->unit->name : '-' }}</td>
        <td>{{ $product->purchase_price ? number_format($product->purchase_price) : '-' }}</td>
        <td>{{ $product->selling_price ? number_format($product->selling_price) : '-' }}</td>
        <td>{{ $product->expire_date ? \Carbon\Carbon::parse($product->expire_date)->format('Y-m-d') : '-' }}</td>
        <td>
            <div class="btn-group">
               <button class="btn btn-primary btn-sm edit-product-btn" 
        data-id="{{ $product->id }}"
        data-name="{{ $product->name }}"
        data-brand="{{ $product->brand }}"
        data-category="{{ $product->category_id }}"
        data-unit="{{ $product->unit_id }}"
        data-quantity="{{ $product->quantity }}"
        data-purchase="{{ $product->purchase_price }}"
        data-selling="{{ $product->selling_price }}"
        data-expire="{{ $product->expire_date }}"
        data-image="{{ $product->image ? asset('storage/'.$product->image) : '' }}">
    <i class="fa fa-edit"></i>
</button>

                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fa fa-tags"></i> Category</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa fa-picture-o"></i> Image</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa fa-usd"></i> Price</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa fa-adn"></i> Attributes</a></li>
                </ul>
               
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Do you want to delete this?')">
                        <i class="fa fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</tbody>

            </table>

        </div> <!-- /card-body -->
    </section>
    <!-- END: ecommerce/product-list -->



 <!-- Trigger button -->


<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true" style="margin-top: 100px;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="addProductForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <!-- Name -->
            <div class="col-md-6">
              <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <!-- Item Category -->
       <div class="col-md-6">
            <label class="form-label">Item Category</label>
            <select class="form-select" name="category_id">
              <option value="">Select Category</option>
              @foreach($categories as $category)
                  <option value="{{ $category->id }}">
                      {{ $category->name }}
                  </option>
              @endforeach
            </select>
          </div>


            <!-- Size -->
            <div class="col-md-6">
              <label for="size" class="form-label">Size</label>
              <input type="text" class="form-control" id="size" name="size">
            </div>

            <!-- Color -->
            <div class="col-md-6">
              <label for="color" class="form-label">Color</label>
              <input type="text" class="form-control" id="color" name="color">
            </div>

            <!-- Brand -->
            <div class="col-md-6">
              <label for="brand" class="form-label">Brand</label>
              <input type="text" class="form-control" id="brand" name="brand">
            </div>

            <!-- Quantity -->
            <div class="col-md-3">
              <label for="quantity" class="form-label">Quantity</label>
              <input type="number" class="form-control" id="quantity" name="quantity" min="0">
            </div>

            <!-- Unit -->
        <div class="col-md-3">
          <label class="form-label">Unit <span class="text-danger">*</span></label>
          <select class="form-select" name="unit_id" required>
            <option value="">Select Unit</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}">
                    {{ $unit->name }} ({{ $unit->short_name }})
                </option>
            @endforeach
          </select>
        </div>


            <!-- Retail Purchasing Price -->
            <div class="col-md-6">
              <label for="purchase_price" class="form-label">Retail Purchasing Price (Tsh) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="purchase_price" name="purchase_price" required min="0">
            </div>

            <!-- Retail Selling Price -->
            <div class="col-md-6">
              <label for="selling_price" class="form-label">Retail Selling Price (Tsh)</label>
              <input type="number" class="form-control" id="selling_price" name="selling_price" min="0">
            </div>

            <!-- Invoice Number -->
            <div class="col-md-6">
              <label for="invoice_number" class="form-label">Invoice Number</label>
              <input type="text" class="form-control" id="invoice_number" name="invoice_number">
            </div>

            <!-- Barcode -->
            <div class="col-md-6">
              <label for="barcode" class="form-label">Barcode</label>
              <input type="text" class="form-control" id="barcode" name="barcode">
            </div>

            <!-- Minimum Quantity -->
            <div class="col-md-6">
              <label for="min_quantity" class="form-label">Minimum Quantity</label>
              <input type="number" class="form-control" id="min_quantity" name="min_quantity" min="0">
            </div>

            <!-- Expire Date -->
            <div class="col-md-6">
              <label for="expire_date" class="form-label">Expire Date</label>
              <input type="date" class="form-control" id="expire_date" name="expire_date">
            </div>

            <!-- Image Upload -->
            <div class="col-md-12">
              <label for="image" class="form-label">Product Image (Optional)</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/*">
              <img id="imagePreview" src="#" alt="Preview Image" style="display:none; max-height:100px; margin-top:10px;">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Add Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts to enhance modal -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('addProductForm');
    var imageInput = document.getElementById('image');
    var imagePreview = document.getElementById('imagePreview');
    var addProductModal = document.getElementById('addProductModal');

    // Preview image before upload
    imageInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // AJAX submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                // Show success message
                alert(data.success);

                // Reset form
                form.reset();
                imagePreview.style.display = 'none';

                // Hide modal
                var modal = bootstrap.Modal.getInstance(addProductModal);
                modal.hide();

                // Optional: reload your product table here via AJAX
            } else {
                // Show validation errors
                let errors = data.errors;
                let message = '';
                for(let field in errors){
                    message += errors[field] + "\n";
                }
                alert(message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong!');
        });
    });
});
</script>

</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true" style="margin-top: 150px;">
  <div class="modal-dialog modal-lg">
    <form id="editProductForm" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <!-- Name -->
            <div class="col-md-6">
              <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_name" name="name" required>
            </div>

            <!-- Brand -->
            <div class="col-md-6">
              <label for="edit_brand" class="form-label">Brand</label>
              <input type="text" class="form-control" id="edit_brand" name="brand">
            </div>

            <!-- Category -->
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select class="form-select" id="edit_category_id" name="category_id">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Unit -->
            <div class="col-md-6">
              <label class="form-label">Unit <span class="text-danger">*</span></label>
              <select class="form-select" id="edit_unit_id" name="unit_id" required>
                <option value="">Select Unit</option>
                @foreach($units as $unit)
                  <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                @endforeach
              </select>
            </div>

            <!-- Quantity -->
            <div class="col-md-6">
              <label for="edit_quantity" class="form-label">Quantity</label>
              <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0">
            </div>

            <!-- Purchase Price -->
            <div class="col-md-6">
              <label for="edit_purchase_price" class="form-label">Purchase Price</label>
              <input type="number" class="form-control" id="edit_purchase_price" name="purchase_price" min="0">
            </div>

            <!-- Selling Price -->
            <div class="col-md-6">
              <label for="edit_selling_price" class="form-label">Selling Price</label>
              <input type="number" class="form-control" id="edit_selling_price" name="selling_price" min="0">
            </div>

            <!-- Expire Date -->
            <div class="col-md-6">
              <label for="edit_expire_date" class="form-label">Expire Date</label>
              <input type="date" class="form-control" id="edit_expire_date" name="expire_date">
            </div>

            <!-- Image Upload -->
            <div class="col-md-12">
              <label for="edit_image" class="form-label">Product Image</label>
              <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
              <img id="edit_image_preview" src="#" alt="Preview Image" style="display:none; max-height:100px; margin-top:10px;">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Update Product</button>
        </div>
      </div>
    </form>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-product-btn');
    const editForm = document.getElementById('editProductForm');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const brand = this.dataset.brand;
            const category = this.dataset.category;
            const unit = this.dataset.unit;
            const quantity = this.dataset.quantity;
            const purchase = this.dataset.purchase;
            const selling = this.dataset.selling;
            const expire = this.dataset.expire;
            const image = this.dataset.image;

            // Fill form fields
            editForm.dataset.id = id; // save ID for AJAX
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_brand').value = brand;
            document.getElementById('edit_category_id').value = category;
            document.getElementById('edit_unit_id').value = unit;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_purchase_price').value = purchase;
            document.getElementById('edit_selling_price').value = selling;
            document.getElementById('edit_expire_date').value = expire;

            const imgPreview = document.getElementById('edit_image_preview');
            if(image){
                imgPreview.src = image;
                imgPreview.style.display = 'block';
            } else {
                imgPreview.style.display = 'none';
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        });
    });

    // Preview uploaded image
    const imageInput = document.getElementById('edit_image');
    const imagePreview = document.getElementById('edit_image_preview');

    imageInput.addEventListener('change', function() {
        if(this.files && this.files[0]){
            const reader = new FileReader();
            reader.onload = function(e){
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // AJAX submit
    editForm.addEventListener('submit', function(e){
        e.preventDefault();

        const id = this.dataset.id;
        const formData = new FormData(this);

        fetch(`/products/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                // Close modal
                const modalEl = document.getElementById('editProductModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                // Optionally: update table row without reloading
                const row = document.querySelector(`#productTable tr[data-id='${id}']`);
                if(row){
                    row.querySelector('td:nth-child(2)').innerText = data.product.name;
                    row.querySelector('td:nth-child(3)').innerText = data.product.quantity ?? 0;
                    row.querySelector('td:nth-child(4)').innerText = data.product.unit_name ?? '-';
                    row.querySelector('td:nth-child(5)').innerText = data.product.purchase_price ?? '-';
                    row.querySelector('td:nth-child(6)').innerText = data.product.selling_price ?? '-';
                    row.querySelector('td:nth-child(7)').innerText = data.product.expire_date ?? '-';
                    if(data.product.image){
                        row.querySelector('td:nth-child(1) img').src = data.product.image;
                    }
                }

                // Show success message
                alert(data.success);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update product.');
        });
    });
});
</script>


</div>


<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    if (jQuery().DataTable) {
        $('#productTable').DataTable({
            responsive: true,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "autoWidth": false
        });
    }
});
</script>
