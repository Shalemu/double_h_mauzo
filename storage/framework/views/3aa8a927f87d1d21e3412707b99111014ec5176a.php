<?php $__env->startSection('title', 'Customers'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="cat__core__widget p-3 h-100 bg-white">

                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h6>Total Customers</h6>
                                    <h4 class="text-primary"><?php echo e($customers->count()); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h6>Total Paid (All Shops)</h6>
                                    <h4 class="text-success"><?php echo e(number_format($customers->sum('total_paid'))); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h6>Total Outstanding Debt</h6>
                                    <h4 class="text-danger"><?php echo e(number_format($customers->sum('total_debt'))); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <input type="text"
                               id="customerSearch"
                               class="form-control form-control-sm w-25"
                               placeholder="Search customer...">

                        <a href="<?php echo e(route('dashboard.admin')); ?>" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>

                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center staff-table">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Shop</th>
                                    <th>Total Purchases</th>
                                    <th>Total Paid</th>
                                    <th>Outstanding Debt</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($index + 1); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('customers.show', $customer->id)); ?>" class="text-primary fw-bold">
                                                <?php echo e($customer->name); ?>

                                            </a>
                                        </td>
                                        <td><?php echo e($customer->phone ?? '-'); ?></td>
                                        <td><?php echo e($customer->shop->name ?? '-'); ?></td>
                                        <td><?php echo e(number_format($customer->total_purchases ?? 0)); ?></td>
                                        <td><?php echo e(number_format($customer->total_paid ?? 0)); ?></td>
                                        <td><?php echo e(number_format($customer->total_debt ?? 0)); ?></td>
                                        <td>
                                            <?php if(($customer->total_debt ?? 0) > 0): ?>
                                                <span class="badge bg-danger">In Debt</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Clear</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('customers.show', $customer->id)); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9">No customers found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
</div>


<script>
document.getElementById('customerSearch').addEventListener('keyup', function () {
    let filter = this.value.toUpperCase();
    document.querySelectorAll('.staff-table tbody tr').forEach(row => {
        row.style.display =
            row.textContent.toUpperCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/admin/customers/index.blade.php ENDPATH**/ ?>