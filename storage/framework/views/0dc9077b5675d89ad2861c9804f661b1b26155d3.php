
<?php $__env->startSection('title', 'Manage Product'); ?>

<?php echo $__env->make('components/staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<?php
    $products = $products ?? collect();
    $runningOutProducts = $runningOutProducts ?? collect();
    $expiringProducts = $expiringProducts ?? collect();
    $expiredProducts = $expiredProducts ?? collect();
    $disposedProducts = $disposedProducts ?? collect();
?>



<div class="container-fluid mt-5">

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
            <a href="<?php echo e(route('products.create')); ?>" class="btn btn-light btn-sm">
                <i class="fa fa-plus"></i> Add Product
            </a>
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

<div class="row mt-4">
    <div class="col-12">
        <!-- Status buttons -->
        <div class="btn-group mb-3" role="group" aria-label="Stock Status Filter">
            <button type="button" class="btn btn-outline-primary active" data-status="running">Running Out</button>
            <button type="button" class="btn btn-outline-primary" data-status="expiring">Expiring</button>
            <button type="button" class="btn btn-outline-primary" data-status="finished">Finished</button>
            <button type="button" class="btn btn-outline-primary" data-status="expired">Expired</button>
            <button type="button" class="btn btn-outline-primary" data-status="disposed">Disposed</button>
        </div>
    </div>
</div>

<!-- EXPORT + SEARCH -->
<div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
        <!-- Export buttons -->
        <div class="btn-group mb-2">
            <button type="button" class="btn btn-success btn-sm" id="export-excel">Export Excel</button>
            <button type="button" class="btn btn-danger btn-sm" id="export-pdf">Export PDF</button>
        </div>

        <!-- Search input -->
        <div class="mb-2" style="width: 200px;">
            <input type="text" id="table-search" class="form-control form-control-sm" placeholder="Search product...">
        </div>
    </div>
</div>

<!-- STATUS TABLES -->
<div class="row">
    <div class="col-12">
        <div id="status-tables">
            <!-- Running Out -->
       <div class="status-table" data-status="running">
    <table class="table table-bordered table-sm">
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
                <tr>
                    <td colspan="4" class="text-center">No running out products found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


            <!-- Expiring -->
            <div class="status-table d-none" data-status="expiring">
                <table class="table table-bordered table-sm">
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
                <tr>
                    <td colspan="3" class="text-center">No products expiring in the next 7 days</td>
                </tr>
            <?php endif; ?>
        </tbody>
                </table>
            </div>

            <!-- Finished -->
            <div class="status-table d-none" data-status="finished">
                <table class="table table-bordered table-sm">
                   <thead class="table-primary h-100">
                        <tr>
                            <th>Item</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
            <tbody>
<?php $__empty_1 = true; $__currentLoopData = $products->where('quantity', 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

    <tr>
        <td><?php echo e($product->name); ?></td>
        <td><?php echo e($product->quantity); ?></td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr>
        <td colspan="2" class="text-center">
            No finished products found
        </td>
    </tr>
<?php endif; ?>
</tbody>


                </table>
            </div>

            <!-- Expired -->
            <div class="status-table d-none" data-status="expired">
                <table class="table table-bordered table-sm">
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
                <tr>
                    <td colspan="3" class="text-center">No expired products found</td>
                </tr>
            <?php endif; ?>
        </tbody>
                </table>
            </div>

            <!-- Disposed -->
            <div class="status-table d-none" data-status="disposed">
                <table class="table table-bordered table-sm">
                      <thead class="table-primary h-100">
                        <tr>
                            <th>Item</th>
                            <th>Disposed Date</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $disposedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($product->name); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($product->disposed_at)->format('Y-m-d') ?? 'N/A'); ?></td>
                    <td><?php echo e($product->quantity); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="3" class="text-center">No disposed products found</td>
                </tr>
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



<script>

    document.querySelectorAll('.btn-group [data-status]').forEach(button => {
    button.addEventListener('click', function () {
        let status = this.dataset.status;

        // Remove active class from all buttons
        document.querySelectorAll('.btn-group [data-status]').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        // Hide all status tables
        document.querySelectorAll('.status-table').forEach(table => table.classList.add('d-none'));

        // Show only the selected table
        let activeTable = document.querySelector(`.status-table[data-status="${status}"]`);
        if (activeTable) {
            activeTable.classList.remove('d-none');
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable without default search box
    let table = $('#productTable').DataTable({
        responsive: true,
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        dom: 'rtip' // remove default search input
    });

    // Custom Search Box
    $('#productSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Entries dropdown
    $('#entriesSelect').on('change', function() {
        let val = parseInt($(this).val());
        table.page.len(val > 0 ? val : table.data().length).draw();
    });
});
</script>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/products/index.blade.php ENDPATH**/ ?>