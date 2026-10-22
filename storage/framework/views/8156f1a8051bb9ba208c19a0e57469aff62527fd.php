<!-- MAIN CONTENT -->
<div class="container-fluid mt-4">

    <!-- SALES REPORT CARD -->
    <div class="card shadow-sm w-100">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Report for <?php echo e($shop->name ?? 'Double H Cosmetics Shop'); ?></h5>
            <!-- Back Button -->
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-sm btn-secondary">
                &larr; Back
            </a>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- EXPORT + SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between flex-wrap align-items-center">
                    <!-- Export buttons -->
                    <div class="btn-group mb-2">
                        <button type="button" class="btn btn-success btn-sm" data-export="excel" data-export-table="#sales-table" data-export-title="<?php echo e(($shop->name ?? 'Shop')); ?> - Sales"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
                        <button type="button" class="btn btn-danger btn-sm" data-export="pdf" data-export-table="#sales-table" data-export-title="<?php echo e(($shop->name ?? 'Shop')); ?> - Sales"><i class="bi bi-file-earmark-pdf"></i> Export PDF</button>
                    </div>

                    <!-- Search input -->
                    <div class="mb-2" style="width: 250px;">
                        <input type="text" id="sales-table-search" class="form-control form-control-sm" placeholder="Search sale...">
                    </div>
                </div>
            </div>

            <!-- SALES TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="sales-table">
                    <thead class="table-primary align-middle">
                        <tr>
                            <th style="width:5%;">SN</th>
                            <th style="width:30%;">Date</th>
                            <th style="width:20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $salesByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $saleData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            
                            <td>
                            <a href="javascript:void(0)" class="view-date" data-url="<?php echo e(route('admin.sales.detail', ['shop' => $shop->id, 'date' => $saleData['date']])); ?>" style="text-decoration: none;">
                                <?php echo e($saleData['date']); ?>

                            </a>
                        </td>

                            </td>
                            <td><?php echo e(number_format($saleData['total'], 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3">No sales for this date.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<!-- Optional: Table Search JS -->
<?php echo $__env->make('pages.sales._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('components.table_export', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\sales\index.blade.php ENDPATH**/ ?>