
<?php
    $number = \App\Helpers\InvoiceNumber::for($invoice);
    $pdfUrl = route('purchases.invoice_pdf', $invoice->id);
    $waText = "Hello " . ($invoice->supplier->name ?? '') . ",\n"
        . "Purchase invoice {$number} from Double H Cosmetics.\n"
        . 'Date: ' . \Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y') . "\n"
        . 'Items: ' . number_format($invoice->items->sum('quantity')) . "\n"
        . 'Total: Tsh ' . number_format($invoice->total_amount, 2) . "\n"
        . 'Paid: Tsh ' . number_format($invoice->amount_paid, 2)
        . ((float) $invoice->remaining_amount > 0 ? "\nBalance: Tsh " . number_format($invoice->remaining_amount, 2) : '')
        . "\n(PDF invoice attached)";
    // WhatsApp needs the number in international form: 0767… → 255767…
    $waUrl = \App\Helpers\Phone::whatsappUrl($invoice->supplier->phone ?? null, $waText);
?>
<div class="d-flex flex-wrap gap-2 invoice-share" data-pdf-url="<?php echo e($pdfUrl); ?>?inline=1" data-file-name="Purchase_Invoice_<?php echo e(\App\Helpers\InvoiceNumber::fileSafe($invoice)); ?>.pdf" data-title="Purchase invoice <?php echo e($number); ?>">
    <a href="<?php echo e($pdfUrl); ?>" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-pdf"></i> Download PDF</a>
    <button type="button" class="btn btn-sm btn-outline-primary js-share-pdf" hidden>
        <i class="bi bi-share"></i> Share PDF
    </button>
    <?php if($waUrl): ?>
        <a href="<?php echo e($waUrl); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success" title="Opens WhatsApp chat with <?php echo e($invoice->supplier->name); ?> — attach the downloaded PDF">
            <i class="bi bi-whatsapp"></i> WhatsApp <?php echo e($invoice->supplier->name ?? 'supplier'); ?>

        </a>
    <?php endif; ?>
    <?php if(auth()->user()->hasPermission('purchases.create')): ?>
        <form method="POST" action="<?php echo e(route('purchases.invoice_email', $invoice->id)); ?>" class="d-inline">
            <?php echo csrf_field(); ?>
            <button class="btn btn-sm btn-outline-secondary" <?php echo e(($invoice->supplier->email ?? null) ? '' : 'disabled'); ?>

                    title="<?php echo e(($invoice->supplier->email ?? null) ? 'Send to ' . $invoice->supplier->email : 'Supplier has no email address'); ?>">
                <i class="bi bi-envelope"></i> Email supplier
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (! $__env->hasRenderedOnce('38fb5e4e-77c7-420d-89ea-b4ec37eadf1a')): $__env->markAsRenderedOnce('38fb5e4e-77c7-420d-89ea-b4ec37eadf1a'); ?>
<script>
// "Share PDF": on phones this opens the share sheet with the PDF attached,
// so it can go straight into WhatsApp. Hidden where file sharing isn't supported.
document.addEventListener('DOMContentLoaded', function () {
    const probe = typeof File !== 'undefined' && navigator.canShare
        && navigator.canShare({ files: [new File([''], 'x.pdf', { type: 'application/pdf' })] });
    document.querySelectorAll('.invoice-share').forEach(function (box) {
        const btn = box.querySelector('.js-share-pdf');
        if (!btn || !probe) return;
        btn.hidden = false;
        btn.addEventListener('click', async function () {
            const label = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Preparing...';
            try {
                const blob = await (await fetch(box.dataset.pdfUrl, { credentials: 'same-origin' })).blob();
                const file = new File([blob], box.dataset.fileName, { type: 'application/pdf' });
                await navigator.share({ files: [file], title: box.dataset.title, text: box.dataset.title });
            } catch (e) {
                if (e.name !== 'AbortError') alert('Could not share the PDF. Use Download PDF instead.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = label;
            }
        });
    });
});
</script>
<?php endif; ?>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\_invoice_share.blade.php ENDPATH**/ ?>