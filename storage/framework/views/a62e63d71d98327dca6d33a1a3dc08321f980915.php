<?php $__env->startSection('title', 'Supplier Products'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Supplier Products</h5>
                <small class="text-muted">What each supplier has delivered, from recorded purchase invoices.</small>
            </div>
            <a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-truck"></i> Suppliers</a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Supplier</label>
                    <select name="supplier" class="form-select form-select-sm">
                        <option value="">All suppliers</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>" <?php echo e(((string) $supplierId === (string) $supplier->id) ? 'selected' : ''); ?>><?php echo e($supplier->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Shop</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All shops</option>
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($shop->id); ?>" <?php echo e(((string) $shopId === (string) $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Product</label>
                    <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search product...">
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Supplier</th>
                            <th>Product</th>
                            <th>Deliveries</th>
                            <th>Total qty bought</th>
                            <th>Cost range (Tsh)</th>
                            <th>Current cost</th>
                            <th>Selling price</th>
                            <th>In stock</th>
                            <th>Last purchased</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-start"><?php echo e($row->supplier); ?><div class="small text-muted"><?php echo e($row->phone); ?></div></td>
                                <td class="text-start"><?php echo e($row->product ?? 'Deleted product'); ?></td>
                                <td><?php echo e($row->deliveries); ?></td>
                                <td><?php echo e(number_format($row->total_qty)); ?></td>
                                <td class="text-nowrap">
                                    <?php echo e(number_format($row->min_cost, 2)); ?><?php if($row->max_cost != $row->min_cost): ?> – <?php echo e(number_format($row->max_cost, 2)); ?><?php endif; ?>
                                </td>
                                <td><?php echo e($row->current_cost !== null ? number_format($row->current_cost, 2) : '—'); ?></td>
                                <td><?php echo e($row->selling_price !== null ? number_format($row->selling_price, 2) : '—'); ?></td>
                                <td><?php echo e($row->in_stock ?? '—'); ?></td>
                                <td class="text-nowrap"><?php echo e(\Carbon\Carbon::parse($row->last_purchased)->format('d M Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="9" class="text-muted py-4">
                                No supplier deliveries found. Products appear here once a purchase is recorded against a supplier.
                                <a href="<?php echo e(route('purchases.create')); ?>">Record a purchase</a>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($rows->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/procurement/supplier_products.blade.php ENDPATH**/ ?>