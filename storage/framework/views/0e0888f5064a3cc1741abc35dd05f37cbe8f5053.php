<div class="card border shadow-sm mb-3">
    <div class="card-body">
        <!-- Header -->
        <h5 class="mb-3">Sales for <?php echo e($date); ?></h5>

        <!-- Sale Type Buttons -->
        <div class="d-flex gap-2 sale-type-group mb-3">
            <input type="radio" class="btn-check" name="sale-type" id="retail" value="retail" checked>
            <label class="btn btn-outline-primary flex-fill py-2" for="retail">
                <i class="fa fa-shopping-basket me-1"></i> Retail
            </label>

            <input type="radio" class="btn-check" name="sale-type" id="wholesale" value="wholesale">
            <label class="btn btn-outline-success flex-fill py-2" for="wholesale">
                <i class="fa fa-cubes me-1"></i> Wholesale
            </label>

            <input type="radio" class="btn-check" name="sale-type" id="both" value="both">
            <label class="btn btn-outline-dark flex-fill py-2" for="both">
                <i class="fa fa-exchange me-1"></i> Both
            </label>
        </div>

        
        <!-- Export Buttons -->
        <div class="d-flex justify-content-end mb-2 gap-2">
            <a href="<?php echo e(route('staff.sales.exportExcel', ['shop' => $shop->id, 'date' => $date, 'type' => 'retail'])); ?>" 
            class="btn btn-sm btn-success" id="export-excel">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="<?php echo e(route('staff.sales.exportPdf', ['shop' => $shop->id, 'date' => $date, 'type' => 'retail'])); ?>" 
            class="btn btn-sm btn-danger">
                <i class="fa fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-striped" id="sales-detail-table">
                <thead class="table-light">
                    <tr>
                        <th>SN</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total (TZS)</th>
                        <th>Sale Type</th>
                    </tr>
                </thead>
                <tbody id="sales-tbody">
                    <?php $__empty_1 = true; $__currentLoopData = $itemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr data-sale-type="<?php echo e($item['sale_type'] ?? 'retail'); ?>">
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($item['product'] ?? 'Unknown'); ?></td>
                            <td><?php echo e($item['quantity'] ?? 0); ?></td>
                            <td><?php echo e(number_format($item['revenue'] ?? 0, 2)); ?></td>
                            <td><?php echo e(ucfirst($item['sale_type'] ?? '-')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr id="no-sales-row">
                            <td colspan="5" class="text-center text-muted">No sales for this date.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Sales pagination" class="mt-2">
            <ul class="pagination justify-content-center" id="sales-pagination"></ul>
        </nav>

        <!-- Back Button -->
        <button class="btn btn-secondary btn-sm mt-2" 
                onclick="document.getElementById('sales-details').style.display='none'; document.getElementById('sales-table-container').style.display='block';">
            ← Back to Sales
        </button>
    </div>
</div>

<?php echo $__env->make('pages.staff.sales._detail_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\staff\sales\detail.blade.php ENDPATH**/ ?>