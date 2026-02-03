@section('title', 'Manage Product')

<div class="cat__content">

    <div class="d-flex justify-content-end gap-1 mt-3 flex-wrap" style="margin-top: 60px; margin-right: 80px;">
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <a href="{{ url('products.upload') }}" class="btn btn-warning btn-sm">
            <i class="fa fa-upload"></i> Upload Excel
        </a>
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
            @php
                // Sample products for testing
                $products = [
                    [
                        'id'=>1, 'img'=>'https://via.placeholder.com/50', 'name'=>'Product A', 'qty'=>12, 'unit'=>'pcs', 
                        'purchase'=>1000, 'sale'=>1500, 'expire'=>'2026-02-28'
                    ],
                    [
                        'id'=>2, 'img'=>'https://via.placeholder.com/50', 'name'=>'Product B', 'qty'=>8, 'unit'=>'pcs', 
                        'purchase'=>800, 'sale'=>1200, 'expire'=>'2026-03-15'
                    ],
                    [
                        'id'=>3, 'img'=>'https://via.placeholder.com/50', 'name'=>'Product C', 'qty'=>0, 'unit'=>'pcs', 
                        'purchase'=>500, 'sale'=>900, 'expire'=>'2026-01-20'
                    ],
                ];
            @endphp
     <div class="d-flex gap-2 mb-2">
                <button class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
                <button class="btn btn-danger btn-sm">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </button>
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
                        <td><img src="{{ $product['img'] }}" alt="Product Image" width="50"></td>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['qty'] }}</td>
                        <td>{{ $product['unit'] }}</td>
                        <td>{{ number_format($product['purchase']) }}</td>
                        <td>{{ number_format($product['sale']) }}</td>
                        <td>{{ $product['expire'] }}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-tags"></i> Category</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-picture-o"></i> Image</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-usd"></i> Price</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-adn"></i> Attributes</a></li>
                                </ul>
                                <a href="#" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                <a href="#" class="btn btn-danger btn-sm" onclick="return confirm('Do you want to delete this?')"><i class="fa fa-trash"></i></a>
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
      <form id="addProductForm" action="{{ url('products.store') }}" method="POST" enctype="multipart/form-data">
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
              <label for="category" class="form-label">Item Category</label>
              <select class="form-select" id="category" name="category">
                <option value="">Select Category</option>
                <option value="Electronics">Electronics</option>
                <option value="Groceries">Groceries</option>
                <option value="Clothing">Clothing</option>
                <option value="Stationery">Stationery</option>
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
              <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
              <select class="form-select" id="unit" name="unit" required>
                <option value="">Select Unit</option>
                <option value="pcs">pcs</option>
                <option value="kg">kg</option>
                <option value="ltr">ltr</option>
                <option value="box">box</option>
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
    var addProductModal = document.getElementById('addProductModal');
    var form = document.getElementById('addProductForm');
    var imageInput = document.getElementById('image');
    var imagePreview = document.getElementById('imagePreview');

    // Reset form when modal opens
    addProductModal.addEventListener('show.bs.modal', function () {
        form.reset();
        imagePreview.style.display = 'none';
        imagePreview.src = '#';
    });

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
