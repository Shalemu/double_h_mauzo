

<!-- MAIN CONTENT -->
<div class="container-fluid mt-4 main-content">

    <!-- SUMMARY CARD -->
    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Shop: <?php echo e($shop->name ?? 'double h cosmetics shop'); ?></h5>
         
            <!-- Back Button -->
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-sm btn-secondary">
                &larr; Back
            </a>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- SUMMARY ROW -->
            <div class="row">

                <!-- TODAY -->
                <div class="col-md-4 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-header bg-warning text-white">Today</div>
                        <div class="card-body p-2">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Sales</td>
                                    <td class="text-end"><?php echo e(number_format($todaySales, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Expenses</td>
                                    <td class="text-end"><?php echo e(number_format($todayExpenses, 2)); ?></td>
                                </tr>
                    <tr class="fw-bold">
                    <td class="<?php echo e((float)$totalProfit < 0 ? 'text-danger' : 'text-success'); ?>">
                        <?php echo e((float)$totalProfit < 0 ? 'Loss' : 'Profit'); ?>

                    </td>
                    <td class="text-end <?php echo e((float)$totalProfit < 0 ? 'text-danger' : 'text-success'); ?>">
                        <?php echo e(number_format($totalProfit, 2)); ?>

                    </td>
                </tr>


                            </table>
                        </div>
                    </div>
                </div>

                <!-- THIS MONTH -->
                <div class="col-md-4 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-header bg-warning text-white"><?php echo e(now()->format('Y, M')); ?></div>
                        <div class="card-body p-2">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Sales</td>
                                    <td class="text-end"><?php echo e(number_format($monthSales, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Expenses</td>
                                    <td class="text-end"><?php echo e(number_format($monthExpenses, 2)); ?></td>
                                </tr>
                          <tr class="fw-bold">
                        <td class="<?php echo e((float)$totalProfit < 0 ? 'text-danger' : 'text-success'); ?>">
                            <?php echo e((float)$totalProfit < 0 ? 'Loss' : 'Profit'); ?>

                        </td>
                        <td class="text-end <?php echo e((float)$totalProfit < 0 ? 'text-danger' : 'text-success'); ?>">
                            <?php echo e(number_format($totalProfit, 2)); ?>

                        </td>
                    </tr>

                            </table>
                        </div>
                    </div>
                </div>

                <!-- CURRENT STOCK -->
                <div class="col-md-4 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-header bg-warning text-white">Current Stock</div>
                        <div class="card-body p-2">
                             <table class="table table-sm mb-0">
                                <tr>
                                    <td>Capital</td>
                                    <td class="text-end"><?php echo e(number_format($currentCapital, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Sales</td>
                                    <td class="text-end"><?php echo e(number_format($totalSales, 2)); ?></td>
                                </tr>
                          <tr class="fw-bold">
                    <td class="<?php echo e((float)$totalProfit < 0 ? 'text-danger' : 'text-success'); ?>">
                        <?php echo e((float)$totalProfit < 0 ? 'Loss' : 'Profit'); ?>

                    </td>
                    <td class="text-end <?php echo e((float)$totalProfit < 0 ? 'text-danger' : 'text-success'); ?>">
                        <?php echo e(number_format($totalProfit, 2)); ?>

                    </td>
                </tr>


                            </table>
                        </div>
                    </div>
                </div>

            </div>

         <!-- STATUS FILTER -->
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
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between flex-wrap align-items-center">
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

</script>

<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/shops/show.blade.php ENDPATH**/ ?>