<?php $__env->startSection('title', 'Manage Product'); ?>
<?php
    $products   = $products   ?? collect();
    $categories = $categories ?? collect();
    $units      = $units      ?? collect();
?>

<div>

    <style>
        .products-page-card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 .25rem .75rem rgba(0,0,0,.06);
        }
        .products-page-card .card-header {
            background: #fff;
            border-bottom: 1px solid #eef0f3;
            padding: 1.1rem 1.4rem;
        }
        .products-page-card thead th {
            font-size: .72rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #6c757d;
            background: #f8f9fb;
            border-bottom-width: 1px;
            white-space: nowrap;
        }
        .products-page-card tbody td {
            vertical-align: middle;
        }
        .products-page-card tbody tr:hover {
            background-color: #f8f9fb;
        }
        #productTable td img {
            border-radius: 8px;
            object-fit: cover;
            height: 44px;
            width: 44px;
        }
    </style>

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3" style="margin-top: 20px;">
        <h3 class="mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Manage Products</h3>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#uploadExcelModal">
              <i class="bi bi-file-earmark-excel"></i> Upload Excel
            </button>
            <a href="<?php echo e(route('products.export.excel')); ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
            </a>
            <a href="<?php echo e(route('products.export.pdf')); ?>" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div>
    </div>

    <!-- Upload Excel Modal -->
<div class="modal fade" id="uploadExcelModal" tabindex="-1" aria-labelledby="uploadExcelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="<?php echo e(route('products.import.excel')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="uploadExcelModalLabel"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Upload Products Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">You can <a href="<?php echo e(route('products.download.template')); ?>">download the sample Excel format</a> to fill in product data.</p>
                <div class="mb-3">
                    <label for="excel_file" class="form-label fw-semibold">Choose Excel file</label>
                    <input type="file" name="excel_file" id="excel_file" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Upload</button>
            </div>
        </div>
    </form>
  </div>
</div>

        
            
             
        

    <!-- START: ecommerce/product-list -->
    <section class="card products-page-card w-100">
        <!-- Card Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-list-ul me-2"></i>Product List</span>
            <span class="text-muted small"><?php echo e($products->count()); ?> item(s)</span>
        </div>

        <!-- Card Body -->
        <div class="card-body p-0">
            <!-- Table -->
            <div class="table-responsive">
           <table class="table table-bordered table-hover align-middle mb-0" id="productTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Img</th>
                        <th>Name</th>
                        <th class="text-center">Available Quantity</th>
                        <th class="text-center">Unit</th>
                        <th class="text-end">Purchase Price</th>
                        <th class="text-end">Retail Price</th>
                        <th class="text-end">Wholesale Price</th>
                        <th class="text-center">Expire Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
              <tbody>
    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr data-id="<?php echo e($product->id); ?>">
        <td>
            <?php if($product->image): ?>
                <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="Product Image">
            <?php else: ?>
                <img src="https://via.placeholder.com/50" alt="No Image">
            <?php endif; ?>
        </td>
        <td class="fw-semibold"><?php echo e($product->name); ?></td>
        <td class="text-center"><?php echo e($product->quantity ?? 0); ?></td>
        <td class="text-center"><?php echo e($product->unit ? $product->unit->name : '-'); ?></td>
        <td class="text-end"><?php echo e($product->purchase_price ? number_format($product->purchase_price) : '-'); ?></td>
        <td class="text-end"><?php echo e($product->selling_price ? number_format($product->selling_price) : '-'); ?></td>
        <td class="text-end"><?php echo e($product->wholesale_price ? number_format($product->wholesale_price) : '-'); ?></td>
        <td class="text-center"><?php echo e($product->expire_date ? \Carbon\Carbon::parse($product->expire_date)->format('Y-m-d') : '-'); ?></td>
        <td class="text-center">
            <div class="btn-group">
               <button class="btn btn-primary btn-sm edit-product-btn" 
        data-id="<?php echo e($product->id); ?>"
        data-name="<?php echo e($product->name); ?>"
        data-brand="<?php echo e($product->brand); ?>"
        data-category="<?php echo e($product->category_id); ?>"
        data-unit="<?php echo e($product->unit_id); ?>"
        data-quantity="<?php echo e($product->quantity); ?>"
        data-purchase="<?php echo e($product->purchase_price); ?>"
        data-selling="<?php echo e($product->selling_price); ?>"
        data-wholesale="<?php echo e($product->wholesale_price); ?>"
        data-expire="<?php echo e($product->expire_date); ?>"
        data-image="<?php echo e($product->image ? asset('storage/'.$product->image) : ''); ?>">
    <i class="fa fa-edit"></i>
</button>

                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fa fa-tags"></i> Category</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa fa-picture-o"></i> Image</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa fa-usd"></i> Price</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa fa-adn"></i> Attributes</a></li>
                </ul>
               
                <form action="<?php echo e(route('products.destroy', $product->id)); ?>" method="POST" style="display:inline-block;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?php echo e($product->id); ?>">
                <i class="fa fa-trash"></i> Delete
            </button>
        </form>
            </div>
        </td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>

            </table>
            </div>

        </div> <!-- /card-body -->
    </section>
    <!-- END: ecommerce/product-list -->

        <script>
    $(document).ready(function() {
        $('#productTable').DataTable({
            "paging": true,            // Enable pagination
            "lengthChange": true,      // Allow user to change number of items per page
            "pageLength": 10,          // Default rows per page
            "searching": true,         // Enable search box
            "ordering": true,          // Enable sorting
            "info": true,              // Show "Showing 1 to X of Y entries"
            "autoWidth": false,
            "responsive": true         // Make table responsive
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;

            Swal.fire({
                title: 'Are you sure?',
                text: "This product will be moved to trash!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, move to trash!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/products/${productId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success){
                            Swal.fire(
                                'Moved!',
                                data.success,
                                'success'
                            );

                            // Remove the row from table
                            const row = button.closest('tr');
                            if(row){
                                row.remove();
                            }
                        } else if(data.error) {
                            Swal.fire('Error', data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    });
                }
            });
        });
    });
});
</script>



 <!-- Trigger button -->


<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form id="addProductForm" action="<?php echo e(route('products.store')); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="addProductModalLabel"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Product</h5>
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
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($category->id); ?>">
                      <?php echo e($category->name); ?>

                  </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($unit->id); ?>">
                    <?php echo e($unit->name); ?> (<?php echo e($unit->short_name); ?>)
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

            <!-- Wholesale Selling Price -->
            <div class="col-md-6">
              <label for="wholesale_price" class="form-label">Wholesale Selling Price (Tsh)</label>
              <input type="number" class="form-control" id="wholesale_price" name="wholesale_price" min="0">
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

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add Product</button>
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
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form id="editProductForm" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="editProductModalLabel"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Product</h5>
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
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>

            <!-- Unit -->
            <div class="col-md-6">
              <label class="form-label">Unit <span class="text-danger">*</span></label>
              <select class="form-select" id="edit_unit_id" name="unit_id" required>
                <option value="">Select Unit</option>
                <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($unit->id); ?>"><?php echo e($unit->name); ?> (<?php echo e($unit->short_name); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
              <label for="edit_selling_price" class="form-label">Retail Selling Price</label>
              <input type="number" class="form-control" id="edit_selling_price" name="selling_price" min="0">
            </div>

            <!-- Wholesale Price -->
            <div class="col-md-6">
              <label for="edit_wholesale_price" class="form-label">Wholesale Selling Price</label>
              <input type="number" class="form-control" id="edit_wholesale_price" name="wholesale_price" min="0">
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
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Product</button>
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
            const wholesale = this.dataset.wholesale;
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
            document.getElementById('edit_wholesale_price').value = wholesale;
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
                    row.querySelector('td:nth-child(7)').innerText = data.product.wholesale_price ?? '-';
                    row.querySelector('td:nth-child(8)').innerText = data.product.expire_date ?? '-';
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




<style>
#productTable .btn-group button {
    margin-right: 5px;
}
</style>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/products/index.blade.php ENDPATH**/ ?>