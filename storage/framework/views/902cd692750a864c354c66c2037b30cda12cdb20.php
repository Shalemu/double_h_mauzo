<?php
    $money = fn ($v) => number_format((float) $v, 2);
    $number = \App\Helpers\InvoiceNumber::for($invoice);
    $owed = (float) $invoice->remaining_amount;
    $isCredit = ($invoice->payment_type ?? null) === 'credit';
    // A pending order shows what was ordered; a received purchase shows what arrived (and what was ordered when they differ).
    $isOrder = $invoice->status !== \App\Models\PurchaseInvoice::RECEIVED;
    $qty = fn ($item) => $isOrder ? (int) ($item->ordered_quantity ?? $item->quantity) : (int) $item->quantity;
    $short = !$isOrder && $invoice->items->contains(fn ($item) => $item->ordered_quantity !== null && (int) $item->ordered_quantity !== (int) $item->quantity);
    $docTitle = $isOrder ? 'Purchase Order' : 'Purchase Invoice';
    // $screen = shown in the browser (invoice page); otherwise rendered to PDF by DomPDF.
    $screen = $screen ?? false;
    $logoFile = public_path('assets/img/logo-pdf.png');
    $logo = $screen ? asset('assets/img/logo-pdf.png') : $logoFile;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($docTitle); ?> <?php echo e($number); ?></title>
    <?php echo $__env->make('pages.purchases._invoice_pdf_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width:80px">
                <?php if(file_exists($logoFile)): ?><img src="<?php echo e($logo); ?>" class="logo" alt="Double H Cosmetics"><?php endif; ?>
            </td>
            <td>
                <div style="font-size:15px; font-weight:bold;" class="brand">Double H Cosmetics</div>
                <div class="muted"><?php echo e($invoice->shop->name ?? ''); ?><?php echo e(($invoice->shop->location ?? null) ? ' · ' . $invoice->shop->location : ''); ?></div>
            </td>
            <td style="text-align:right">
                <h1><?php echo e(strtoupper($docTitle)); ?></h1>
                <div style="font-size:13px; margin-top:4px;"><strong><?php echo e($number); ?></strong></div>
                <div class="muted"><?php echo e(\Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y, H:i')); ?></div>
                <div style="margin-top:4px;">
                    <?php if($invoice->status === \App\Models\PurchaseInvoice::CANCELLED): ?>
                        <span class="badge due">CANCELLED</span>
                    <?php elseif($isOrder): ?>
                        <span class="badge due">AWAITING DELIVERY</span>
                    <?php elseif($isCredit && $owed > 0): ?>
                        <span class="badge due">CREDIT · OWED Tsh <?php echo e($money($owed)); ?></span>
                    <?php else: ?>
                        <span class="badge paid">PAID</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <table style="margin:16px 0;">
        <tr>
            <td style="width:50%; padding-right:6px;">
                <div class="box">
                    <div class="label">Supplier</div>
                    <div style="font-weight:bold; font-size:12px;"><?php echo e($invoice->supplier->name ?? '—'); ?></div>
                    <?php if($invoice->supplier->phone ?? null): ?><div><?php echo e($invoice->supplier->phone); ?></div><?php endif; ?>
                    <?php if($invoice->supplier->email ?? null): ?><div><?php echo e($invoice->supplier->email); ?></div><?php endif; ?>
                    <?php if($invoice->supplier->address ?? null): ?><div class="muted"><?php echo e($invoice->supplier->address); ?></div><?php endif; ?>
                </div>
            </td>
            <td style="width:50%; padding-left:6px;">
                <div class="box">
                    <div class="label"><?php echo e($isOrder ? 'Deliver to' : 'Delivered to'); ?></div>
                    <div style="font-weight:bold; font-size:12px;"><?php echo e($invoice->shop->name ?? '—'); ?></div>
                    <div class="muted"><?php echo e($invoice->shop->location ?? ''); ?></div>
                    <div>Payment: <?php echo e($isCredit ? 'Credit' : 'Cash'); ?></div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:24px">#</th>
                <th>Product</th>
                <th style="width:60px">Unit</th>
                <?php if($short): ?><th class="num" style="width:60px">Ordered</th><?php endif; ?>
                <th class="num" style="width:60px"><?php echo e($short ? 'Received' : 'Qty'); ?></th>
                <th class="num" style="width:95px">Unit price (Tsh)</th>
                <th class="num" style="width:105px">Amount (Tsh)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($item->product->name ?? 'Deleted product'); ?></td>
                    <td><?php echo e($item->product->unit->short_name ?? '—'); ?></td>
                    <?php if($short): ?><td class="num"><?php echo e(number_format($item->ordered_quantity)); ?></td><?php endif; ?>
                    <td class="num"><?php echo e(number_format($qty($item))); ?></td>
                    <td class="num"><?php echo e($money($item->purchase_price)); ?></td>
                    <td class="num"><?php echo e($money($qty($item) * $item->purchase_price)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:55%"></td>
            <td>
                <table class="totals">
                    <tr><td class="muted">Items</td><td class="num"><?php echo e(number_format($invoice->items->sum($qty))); ?></td></tr>
                    <?php if($isOrder): ?>
                    <tr class="grand"><td>Order value</td><td class="num">Tsh <?php echo e($money($invoice->items->sum(fn ($item) => $qty($item) * $item->purchase_price))); ?></td></tr>
                    <?php else: ?>
                    <tr class="grand"><td>Total</td><td class="num">Tsh <?php echo e($money($invoice->total_amount)); ?></td></tr>
                    <tr><td class="muted">Paid</td><td class="num"><?php echo e($money($invoice->amount_paid)); ?></td></tr>
                    <tr><td class="<?php echo e($owed > 0 ? 'brand' : 'muted'); ?>">Balance owed</td><td class="num <?php echo e($owed > 0 ? 'brand' : ''); ?>"><?php echo e($money($owed)); ?></td></tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by Double H Cosmetics on <?php echo e(now()->format('d M Y, H:i')); ?> · Thank you for your business.
    </div>
</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\invoice_pdf.blade.php ENDPATH**/ ?>