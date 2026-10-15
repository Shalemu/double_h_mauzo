<?php $__env->startSection('title', 'Credit'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Customers owe you (Tsh)</div>
            <div class="fs-4 fw-bold text-primary"><?php echo e(number_format($customers->sum('owed'), 2)); ?></div>
            <div class="small text-muted"><?php echo e($customers->count()); ?> customer(s)</div>
        </div></div></div>
        <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">You owe suppliers (Tsh)</div>
            <div class="fs-4 fw-bold text-danger"><?php echo e(number_format($invoices->sum('remaining_amount'), 2)); ?></div>
            <div class="small text-muted"><?php echo e($invoices->count()); ?> unpaid invoice(s)</div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'customers' ? 'active' : ''); ?>" href="<?php echo e(route('admin.credit', ['tab' => 'customers', 'shop' => $shopId])); ?>">
                        <i class="bi bi-people me-1"></i> Customer credit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'suppliers' ? 'active' : ''); ?>" href="<?php echo e(route('admin.credit', ['tab' => 'suppliers', 'shop' => $shopId])); ?>">
                        <i class="bi bi-truck me-1"></i> Supplier credit
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <?php echo $__env->make('pages.overview._shop_filter', ['keep' => ['tab' => $tab]], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <?php if($tab === 'customers'): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr><th class="text-start">Customer</th><th>Phone</th><th>Unpaid sales</th><th>Owed (Tsh)</th><th>Oldest debt</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-start"><?php echo e($customer->name); ?></td>
                                    <td><?php echo e($customer->phone ?: '—'); ?></td>
                                    <td><?php echo e($customer->open_sales); ?></td>
                                    <td class="fw-semibold"><?php echo e(number_format($customer->owed, 2)); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($customer->oldest)->format('d M Y')); ?>

                                        <div class="small text-muted"><?php echo e(\Carbon\Carbon::parse($customer->oldest)->diffForHumans()); ?></div></td>
                                    <td>
                                        <a href="<?php echo e(route('customers.show', $customer->id)); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-cash"></i> View / record payment
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="text-muted py-4">No customer owes anything.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr><th>Invoice</th><th>Supplier</th><th>Shop</th><th>Purchased</th><th>Total (Tsh)</th><th>Paid (Tsh)</th><th>Balance (Tsh)</th><th style="min-width:220px">Pay</th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><a href="<?php echo e(route('purchases.history', $invoice->id)); ?>"><?php echo e($invoice->invoice_number ?: '#' . $invoice->id); ?></a></td>
                                    <td><?php echo e($invoice->supplier->name ?? '—'); ?></td>
                                    <td><?php echo e($invoice->shop->name ?? '—'); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($invoice->purchased_at ?? $invoice->created_at)->format('d M Y')); ?></td>
                                    <td><?php echo e(number_format($invoice->total_amount, 2)); ?></td>
                                    <td><?php echo e(number_format($invoice->amount_paid, 2)); ?></td>
                                    <td class="fw-semibold text-danger"><?php echo e(number_format($invoice->remaining_amount, 2)); ?></td>
                                    <td>
                                        <form method="POST" action="<?php echo e(route('purchases.deposit')); ?>" class="d-flex gap-1">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="purchase_invoice_id" value="<?php echo e($invoice->id); ?>">
                                            <input type="number" name="amount" class="form-control form-control-sm" min="1" step="0.01"
                                                   max="<?php echo e($invoice->remaining_amount); ?>" placeholder="Amount" required
                                                   aria-label="Payment amount for <?php echo e($invoice->invoice_number); ?>">
                                            <button class="btn btn-sm btn-success">Pay</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="8" class="text-muted py-4">No unpaid supplier invoices.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\overview\credit.blade.php ENDPATH**/ ?>