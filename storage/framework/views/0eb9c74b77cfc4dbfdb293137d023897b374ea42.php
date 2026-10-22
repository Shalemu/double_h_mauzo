<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Purchase Returns for <?php echo e($shop->name); ?></h5>
                <small class="text-muted">Select a date below to view its purchased items and process a return.</small>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>S/N</th>
                            <th>Date</th>
                            <th>Total Purchased</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $purchasesByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $purchaseData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td>
                                <a href="javascript:void(0)"
                                   class="view-purchase-date"
                                   data-url="<?php echo e(route('admin.purchase_returns.detail', [
                                       'shop' => $shop->id,
                                       'date' => $purchaseData['date']
                                   ])); ?>">
                                    <?php echo e($purchaseData['date']); ?>

                                </a>
                            </td>
                            <td><?php echo e(number_format($purchaseData['total_amount'],2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3">No purchases found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- AJAX CONTENT -->
    <div id="purchase-return-detail"></div>

</div>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php echo $__env->make('pages.purchase_returns._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchase_returns\index.blade.php ENDPATH**/ ?>