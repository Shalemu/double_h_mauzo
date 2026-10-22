<h6>Invoices on <?php echo e($date); ?></h6>
<div class="table-responsive mb-4">
<table class="table table-sm table-bordered text-center align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th>Invoice</th>
            <th>Time</th>
            <th>Supplier</th>
            <th>Units</th>
            <th>Total (Tsh)</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $invoices ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $owed = (float) $invoice->remaining_amount; ?>
            <tr>
                <td class="fw-semibold"><?php echo e(\App\Helpers\InvoiceNumber::for($invoice)); ?></td>
                <td><?php echo e(\Carbon\Carbon::parse($invoice->purchased_at)->format('H:i')); ?></td>
                <td><?php echo e($invoice->supplier->name ?? '-'); ?></td>
                <td><?php echo e(number_format((int) $invoice->units)); ?></td>
                <td><?php echo e(number_format($invoice->total_amount, 2)); ?></td>
                <td>
                    <?php if(($invoice->payment_type ?? null) === 'credit' && $owed > 0): ?>
                        <span class="badge bg-warning text-dark">Owes <?php echo e(number_format($owed)); ?></span>
                    <?php else: ?>
                        <span class="badge bg-success">Paid</span>
                    <?php endif; ?>
                </td>
                <td class="text-nowrap">
                    <a href="<?php echo e(route('purchases.history', $invoice->id)); ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> View</a>
                    <a href="<?php echo e(route('purchases.invoice_pdf', $invoice->id)); ?>" class="btn btn-sm btn-outline-danger" aria-label="Download PDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="text-muted">No invoices on this day.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<h6>Items purchased on <?php echo e($date); ?></h6>
<table class="table table-sm table-bordered text-center">
    <thead>
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Supplier</th>
            <th>Invoice</th>
            <th>Qty</th>
            <th>Retail Price</th>
            <th>Wholesale Price</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            
            <td><?php echo e($index + 1); ?></td>

            
            <td><?php echo e($p->product?->name ?? '-'); ?></td>

            
            <td><?php echo e($p->invoice?->supplier?->name ?? '-'); ?></td>

            
            <td>
                <?php if($p->invoice): ?>
                    <a href="<?php echo e(route('purchases.history', $p->invoice->id)); ?>"><?php echo e(\App\Helpers\InvoiceNumber::for($p->invoice)); ?></a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>

            
            <td><?php echo e($p->quantity ?? 0); ?></td>

            
            <td><?php echo e($p->product?->selling_price ? number_format($p->product->selling_price, 2) : '-'); ?></td>

            
            <td><?php echo e($p->product?->wholesale_price ? number_format($p->product->wholesale_price, 2) : '-'); ?></td>

            
            <td><?php echo e(number_format($p->purchase_price ?? 0, 2)); ?></td>

            
            <td><?php echo e(number_format(($p->quantity ?? 0) * ($p->purchase_price ?? 0), 2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\detail.blade.php ENDPATH**/ ?>