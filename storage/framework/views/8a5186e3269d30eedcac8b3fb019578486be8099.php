<?php
    $products             = $products             ?? collect();
    $categories           = $categories           ?? collect();
    $units                = $units                ?? collect();
    $shops                = $shops                ?? collect();
    $isAdminTier          = $isAdminTier          ?? false;
    $productsTotal        = $productsTotal        ?? null;
    $productsDisplayLimit = $productsDisplayLimit ?? null;
    $activeShop           = $activeShop           ?? null;
    $globalMode           = $globalMode           ?? $isAdminTier;
    $productFilter         = $productFilter         ?? 'all';
    $productPageTitle = [
        'all' => 'Manage Products',
        'in_stock' => 'In Stock Products',
        'expired' => 'Expired Products',
        'out_of_stock' => 'Out of Stock Products',
        'running_out' => 'Running Out Products',
        'expiring' => 'Expiring within 1 year',
    ][$productFilter] ?? 'Manage Products';
?>
<?php $__env->startSection('title', $productPageTitle); ?>

<?php echo $__env->make('pages.products._index_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php if($isAdminTier): ?>
    <div class="alert <?php echo e($activeShop ? 'products-shop-banner' : 'alert-secondary'); ?> d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <?php if($activeShop): ?>
                <i class="bi bi-shop me-1"></i> Working in <strong><?php echo e($activeShop->name); ?></strong>
            <?php else: ?>
                <i class="bi bi-globe me-1"></i> Viewing <strong>all shops</strong>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="POST" action="<?php echo e(route('admin.activeShop.set')); ?>" class="d-flex gap-2">
                <?php echo csrf_field(); ?>
                <select name="shop_id" class="form-select" onchange="this.form.submit()" style="min-width:180px;">
                    <option value="">-- Switch shop --</option>
                    <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>" <?php echo e($activeShop && $activeShop->id === $s->id ? 'selected' : ''); ?>><?php echo e($s->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
            <?php if($activeShop): ?>
                <form method="POST" action="<?php echo e(route('admin.activeShop.clear')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn shop-view-all-btn">View all shops</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div>

    <?php echo $__env->make('pages.products._index_styles_2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>


    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-end align-items-center flex-wrap gap-2 mb-3" style="margin-top: 20px;">
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#uploadExcelModal">
              <i class="bi bi-file-earmark-excel"></i> Upload Excel
            </button>
            <a href="<?php echo e(route('products.export.excel', $productFilter !== 'all' ? ['filter' => $productFilter] : [])); ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
            </a>
            <a href="<?php echo e(route('products.export.pdf', $productFilter !== 'all' ? ['filter' => $productFilter] : [])); ?>" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="<?php echo e(route('brands.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-award"></i> Brand
            </a>
            <a href="<?php echo e(route('units.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-rulers"></i> Unit of Measure
            </a>
            <a href="<?php echo e(route('products.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Product
            </a>
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

                <?php if($globalMode): ?>
                <div class="mb-3">
                    <label for="import_shop_id" class="form-label fw-semibold">Import into shop <span class="text-danger">*</span></label>
                    <select name="shop_id" id="import_shop_id" class="form-select" required>
                        <option value="">Select Shop</option>
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($shop->id); ?>"><?php echo e($shop->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php endif; ?>

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






    <?php if($globalMode): ?>
    <!-- Bulk Shop Assignment -->
    <form id="bulkAssignForm" action="<?php echo e(route('products.bulkAssignShop')); ?>" method="POST"
          class="border rounded-3 p-3 mb-3 bg-light d-flex flex-wrap align-items-end gap-2">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="assign_all_unassigned" id="assign_all_unassigned" value="0">

        <div>
            <label class="form-label fw-semibold mb-1">Assign to shop</label>
            <select name="shop_id" id="bulk_shop_id" class="form-select" required style="min-width: 220px;">
                <option value="">Select Shop</option>
                <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($shop->id); ?>"><?php echo e($shop->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <button type="button" id="assignSelectedBtn" class="btn btn-primary">
            <i class="bi bi-check2-square"></i> Assign Selected (<span id="selectedCount">0</span>)
        </button>

        <button type="button" id="assignAllUnassignedBtn" class="btn btn-outline-warning">
            <i class="bi bi-exclamation-triangle"></i> Assign ALL Unassigned Products
        </button>
    </form>
    <?php endif; ?>

    <!-- START: ecommerce/product-list -->
    <section class="card products-page-card w-100">
        <!-- Card Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-list-ul me-2"></i><?php echo e($productFilter === 'all' ? 'Product List' : $productPageTitle); ?>

                <?php if($productFilter !== 'all'): ?>
                    <span class="badge bg-primary ms-2">
                        <?php echo e(['in_stock' => 'In Stock', 'expired' => 'Expired', 'out_of_stock' => 'Out of Stock', 'running_out' => 'Running Out', 'expiring' => 'Expiring'][$productFilter] ?? 'Filtered'); ?>

                    </span>
                    <a href="<?php echo e(route('products.index')); ?>" class="small fw-normal ms-2">Clear filter</a>
                <?php endif; ?>
            </span>
            <?php if($productsTotal !== null && $productsTotal > $products->count()): ?>
                <span class="text-muted small">
                    Showing latest <?php echo e($products->count()); ?> of <?php echo e(number_format($productsTotal)); ?> item(s) —
                    use search/export or the bulk-assign tool above for the full set.
                </span>
            <?php else: ?>
                <span class="text-muted small"><?php echo e($products->count()); ?> item(s)</span>
            <?php endif; ?>
        </div>

        <!-- Card Body -->
        <div class="card-body p-0">
            <!-- Table -->
            <div class="table-responsive">
           <table class="table table-bordered table-hover align-middle mb-0" id="productTable" style="width: 100%;">
                <thead>
                    <tr>
                        <?php if($globalMode): ?>
                        <th><input type="checkbox" id="selectAllProducts"></th>
                        <?php endif; ?>
                        <th>Img</th>
                        <th>Name</th>
                        <?php if($globalMode): ?>
                        <th>Shop</th>
                        <?php endif; ?>
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
        <?php if($globalMode): ?>
        <td><input type="checkbox" class="product-checkbox" value="<?php echo e($product->id); ?>"></td>
        <?php endif; ?>
        <td>
            <?php if($product->image): ?>
                <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="Product Image">
            <?php else: ?>
                <img src="https://via.placeholder.com/50" alt="No Image">
            <?php endif; ?>
        </td>
        <td class="fw-semibold"><?php echo e($product->name); ?></td>
        <?php if($globalMode): ?>
        <td>
            <?php if($product->shop): ?>
                <?php echo e($product->shop->name); ?>

            <?php else: ?>
                <span class="badge bg-warning text-dark">Unassigned</span>
            <?php endif; ?>
        </td>
        <?php endif; ?>
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
        data-shop="<?php echo e($product->shop_id); ?>"
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

        <?php echo $__env->make('pages.products._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>




</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form id="editProductForm" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="editProductModalLabel"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2">
            <!-- Name -->
            <div class="col-lg-4 col-md-6">
              <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_name" name="name" required>
            </div>

            <!-- Brand -->
            <div class="col-lg-4 col-md-6">
              <label for="edit_brand" class="form-label">Brand</label>
              <select class="form-select" id="edit_brand" name="brand">
                <option value="">-- select brand --</option>
                <?php $__currentLoopData = \App\Models\Brand::orderBy('name')->pluck('name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brandName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($brandName); ?>"><?php echo e($brandName); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>

            <!-- Category -->
            <div class="col-lg-4 col-md-6">
              <label class="form-label">Category</label>
              <select class="form-select" id="edit_category_id" name="category_id">
                <option value="">Select Category</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>

            <?php if($globalMode): ?>
            <!-- Shop -->
            <div class="col-lg-4 col-md-6">
              <label class="form-label">Shop <span class="text-danger">*</span></label>
              <select class="form-select" id="edit_shop_id" name="shop_id" required>
                <option value="">Select Shop</option>
                <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($shop->id); ?>"><?php echo e($shop->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <?php endif; ?>

            <!-- Unit -->
            <div class="col-lg-4 col-md-6">
              <label class="form-label">Unit <span class="text-danger">*</span></label>
              <select class="form-select" id="edit_unit_id" name="unit_id" required>
                <option value="">Select Unit</option>
                <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($unit->id); ?>"><?php echo e($unit->name); ?> (<?php echo e($unit->short_name); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>

            <!-- Quantity -->
            <div class="col-lg-4 col-md-6">
              <label for="edit_quantity" class="form-label">Quantity</label>
              <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0">
            </div>

            <!-- Purchase Price -->
            <div class="col-lg-4 col-md-6">
              <label for="edit_purchase_price" class="form-label">Purchase Price</label>
              <input type="number" class="form-control" id="edit_purchase_price" name="purchase_price" min="0">
            </div>

            <!-- Selling Price -->
            <div class="col-lg-4 col-md-6">
              <label for="edit_selling_price" class="form-label">Retail Selling Price</label>
              <input type="number" class="form-control" id="edit_selling_price" name="selling_price" min="0">
            </div>

            <!-- Wholesale Price -->
            <div class="col-lg-4 col-md-6">
              <label for="edit_wholesale_price" class="form-label">Wholesale Selling Price</label>
              <input type="number" class="form-control" id="edit_wholesale_price" name="wholesale_price" min="0">
            </div>

            <!-- Expire Date -->
            <div class="col-lg-4 col-md-6">
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

<?php echo $__env->make('pages.products._index_scripts_2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


</div>




<style>
#productTable .btn-group button {
    margin-right: 5px;
}
</style>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/products/index.blade.php ENDPATH**/ ?>