
<?php $__env->startSection('title', 'Manage Product'); ?>

<?php $__env->startSection('content'); ?>

<?php
    $products = $products ?? collect();
    $runningOutProducts = $runningOutProducts ?? collect();
    $expiringProducts = $expiringProducts ?? collect();
    $expiredProducts = $expiredProducts ?? collect();
    $finishedProducts = $finishedProducts ?? $products->where('quantity', 0);
    $disposedProducts = $disposedProducts ?? collect();
?>



<div class="container-fluid mt-5" style="max-width: 1400px; margin:auto">

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <a href="<?php echo e(route('products.export.excel')); ?>" class="btn btn-success btn-sm">
            <i class="fa fa-file-excel-o"></i> Export Excel
        </a>
        <a href="<?php echo e(route('products.export.pdf')); ?>" class="btn btn-danger btn-sm">
            <i class="fa fa-file-pdf-o"></i> Export PDF
        </a>
    </div>

    <!-- Product List Card -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0">Product List</h5>

        </div>

        <div class="card-body">
            <!-- Top Controls -->
            <div class="d-flex justify-content-between mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0">Show 
                        <select id="entriesSelect" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                        entries
                    </label>
                </div>
                <div>
                    <input type="search" id="productSearch" class="form-control form-control-sm w-auto" placeholder="Search Products">
                </div>
            </div>

            <!-- Product Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center" id="productTable">
                    <thead class="table-light">
                        <tr>
                            <th>S/N</th>
                            <th>Img</th>
                            <th>Name</th>
                            <th>Available Quantity</th>
                            <th>Unit</th>
                            <th>Sale Price</th>
                            <th>Expire Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td>
                                <?php if($product->image): ?>
                                    <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="Product Image" class="img-thumbnail" style="width:50px; height:50px;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/50" alt="No Image" class="img-thumbnail">
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($product->name); ?></td>
                            <td><?php echo e($product->quantity ?? 0); ?></td>
                            <td><?php echo e($product->unit?->name ?? '-'); ?></td>
                            <td><?php echo e($product->selling_price ? number_format($product->selling_price) : '-'); ?></td>
                            <td><?php echo e($product->expire_date ? \Carbon\Carbon::parse($product->expire_date)->format('Y-m-d') : '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">No products found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="row mt-4" style="max-width: 1400px; margin:auto">
    <div class="col-12">
        <!-- Status buttons -->
        <div class="btn-group mb-3" role="group" aria-label="Stock Status Filter">
            <button type="button" class="btn btn-outline-primary active" data-status="running">Running Out</button>
            <button type="button" class="btn btn-outline-primary" data-status="expiring">Expiring</button>
            <button type="button" class="btn btn-outline-primary" data-status="finished">Finished</button>
            <button type="button" class="btn btn-outline-primary" data-status="expired">Expired</button>
        </div>
    </div>
</div>

<!-- EXPORT + SEARCH -->
<div class="card shadow-sm" style="max-width: 1400px; margin:auto">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
        <!-- Export buttons -->
        <div class="btn-group mb-2">
            <button type="button" class="btn btn-success btn-sm" data-export="excel" data-export-table="#productTable" data-export-title="Shop products"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
            <button type="button" class="btn btn-danger btn-sm" data-export="pdf" data-export-table="#productTable" data-export-title="Shop products"><i class="bi bi-file-earmark-pdf"></i> Export PDF</button>
        </div>

        <!-- Search input -->
        <div class="mb-2" style="width: 200px;">
            <input type="text" id="table-search" class="form-control form-control-sm" placeholder="Search product...">
        </div>
    </div>
</div>

<!-- STATUS TABLES -->
<div class="row" style="max-width: 1400px; margin:auto">
    <div class="col-12">
        <div id="status-tables">
            <!-- Running Out -->
       <div class="status-table" data-status="running">
    <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="runningOutTable">
        <thead class="table-primary h-100">
            <tr>
                <th>Item</th>
                <th>Stock</th>
                <th>Last Purchasing Price</th>
                <th>Last Selling Price</th>
            </tr>
        </thead>
        <tbody>
           <?php $__empty_1 = true; $__currentLoopData = $runningOutProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($product->name); ?></td>
                    <td><?php echo e($product->quantity); ?></td>
                    <td><?php echo e($product->purchase_price); ?></td>
                    <td><?php echo e($product->selling_price); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>


            <!-- Expiring -->
            <div class="status-table d-none" data-status="expiring">
                <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-primary h-100">
                        <tr>
                            <th>Item</th>
                            <th>Expiry Date</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
             <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $expiringProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($product->name); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($product->expire_date)->format('Y-m-d')); ?></td>
                    <td><?php echo e($product->quantity); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php endif; ?>
        </tbody>
                </table>
                </div>
            </div>

            <!-- Finished -->
            <div class="status-table d-none" data-status="finished">
                <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0" id="finishedTable">
                   <thead class="table-primary h-100">
                        <tr>
                            <th>Item</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
            <tbody>
<?php $__empty_1 = true; $__currentLoopData = $finishedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

    <tr>
        <td><?php echo e($product->name); ?></td>
        <td><?php echo e($product->quantity); ?></td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<?php endif; ?>
</tbody>


                </table>
                </div>
            </div>

            <!-- Expired -->
            <div class="status-table d-none" data-status="expired">
                <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0" id="expiredTable">
                      <thead class="table-primary h-100">
                        <tr>
                            <th>Item</th>
                            <th>Expiry Date</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $expiredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($product->name); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($product->expire_date)->format('Y-m-d')); ?></td>
                    <td><?php echo e($product->quantity); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php endif; ?>
        </tbody>
                </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

        </div>
    </div>

</div>



<?php echo $__env->make('pages.staff.products._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('components.table_export', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\staff\products\index.blade.php ENDPATH**/ ?>