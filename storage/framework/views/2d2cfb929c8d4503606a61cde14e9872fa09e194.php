<!-- resources/views/dashboard/sales/detail.blade.php -->

<div class="container-fluid mt-4 main-content">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Daily Item Sales Detail for <?php echo e(\Carbon\Carbon::parse($date)->format('d M Y')); ?>

                (<?php echo e($shop->name ?? 'Double H Cosmetics Shop'); ?>)
            </h5>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-sm btn-secondary">&larr; Back</a>
        </div>

        <div class="card-body">

            <!-- SALE TYPE FILTER -->
            <div class="d-flex gap-2 mb-3">
                <input type="radio" class="btn-check" name="sale-type" id="retail" value="retail" checked>
                <label class="btn btn-outline-primary" for="retail">Retail</label>

                <input type="radio" class="btn-check" name="sale-type" id="wholesale" value="wholesale">
                <label class="btn btn-outline-success" for="wholesale">Wholesale</label>

                <input type="radio" class="btn-check" name="sale-type" id="both" value="both">
                <label class="btn btn-outline-dark" for="both">Both</label>
            </div>

            <!-- EXPORT + SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between flex-wrap align-items-center">

                    <!-- Export -->
                    <div class="btn-group mb-2">
                        <a href="<?php echo e(route('sales.export.excel', [$shop->id, $date])); ?>"
                           class="btn btn-success btn-sm"
                           id="export-excel"
                           data-base-url="<?php echo e(route('sales.export.excel', [$shop->id, $date])); ?>">
                            Export Excel
                        </a>

                        <a href="<?php echo e(route('sales.export.pdf', [$shop->id, $date])); ?>"
                           class="btn btn-danger btn-sm"
                           id="export-pdf"
                           data-base-url="<?php echo e(route('sales.export.pdf', [$shop->id, $date])); ?>">
                            Export PDF
                        </a>
                    </div>

                    <!-- Search -->
                    <div style="width: 250px;">
                        <input type="text" id="table-search"
                               class="form-control form-control-sm"
                               placeholder="Search sale...">
                    </div>

                </div>
            </div>

            <!-- SALES TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="sales-table">
                    <thead class="table-primary align-middle">
                        <tr>
                            <th>S/N</th>
                            <th>Item</th>
                            <th>Quantity Sold</th>
                            <th>Total (TZS)</th>
                            <th>Sold by</th>
                            <th>Sale Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sales-tbody">
                        <?php $__empty_1 = true; $__currentLoopData = $itemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr data-sale-type="<?php echo e(strtolower(trim($row['sale_type'] ?? 'retail'))); ?>">
                            <td><?php echo e($index + 1); ?></td>
                            <td class="text-start"><?php echo e($row['product']); ?></td>
                            <td><?php echo e($row['quantity']); ?></td>
                            <td class="fw-bold"><?php echo e(number_format($row['revenue'], 2)); ?></td>
                            <td class="text-start"><?php echo e($row['staff']); ?></td>
                            <td><?php echo e(ucfirst($row['sale_type'] ?? 'Retail')); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info view-receipt"
                                        data-product="<?php echo e($row['product']); ?>"
                                        data-staff="<?php echo e($row['staff']); ?>">
                                    View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr id="no-sales-row">
                            <td colspan="7" class="text-center text-muted">
                                No items sold on this date.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/sales/detail.blade.php ENDPATH**/ ?>