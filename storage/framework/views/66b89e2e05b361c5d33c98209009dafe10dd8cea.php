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

<script>
document.addEventListener('click', function (e) {

    // =========================
    // LOAD DETAIL (AJAX)
    // =========================
    const dateLink = e.target.closest('.view-purchase-date');
    if (dateLink) {
        fetch(dateLink.dataset.url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            document.getElementById('purchase-return-detail').innerHTML = html;
        });

        return;
    }

    // =========================
    // RETURN BUTTON
    // =========================
    const returnBtn = e.target.closest('.return-purchase-btn');
    if (!returnBtn) return;

    const purchaseItemId = returnBtn.dataset.purchaseItemId;
    const productId = returnBtn.dataset.productId;
    const productName = returnBtn.dataset.productName;
    const maxQty = parseInt(returnBtn.dataset.quantity || 1);
    const unitPrice = parseFloat(returnBtn.dataset.price || 0);

    Swal.fire({
        title: 'Return Purchase',
        width: 450,
        showCancelButton: true,
        confirmButtonText: 'Return',

        html: `
            <div style="text-align:left">

                <label style="font-weight:600">Product</label>
                <input type="text" value="${productName}"
                    style="width:100%;padding:8px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px;background:#eee"
                    readonly>

                <label style="font-weight:600">Quantity (max ${maxQty})</label>
                <input type="number" id="qty" value="1" min="1" max="${maxQty}"
                    style="width:100%;padding:8px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px">

                <label style="font-weight:600">Amount</label>
                <input type="number" id="amount" value="${unitPrice}"
                    style="width:100%;padding:8px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px;background:#eee"
                    readonly>

                <label style="font-weight:600">Reason</label>
                <textarea id="reason"
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px"></textarea>

            </div>
        `,

        didOpen: () => {
            const qtyInput = document.getElementById('qty');
            const amountInput = document.getElementById('amount');

            qtyInput.addEventListener('input', () => {
                let qty = parseInt(qtyInput.value) || 1;

                if (qty < 1) qty = 1;
                if (qty > maxQty) qty = maxQty;

                qtyInput.value = qty;
                amountInput.value = qty * unitPrice;
            });
        },

        preConfirm: () => {

            const qty = parseInt(document.getElementById('qty').value);
            const amount = parseFloat(document.getElementById('amount').value);

            if (!qty || qty < 1) {
                Swal.showValidationMessage('Invalid quantity');
                return false;
            }

            if (qty > maxQty) {
                Swal.showValidationMessage('Quantity exceeds available');
                return false;
            }

            return {
                purchase_item_id: purchaseItemId,
                product_id: productId,
                shop_id: "<?php echo e($shop->id); ?>",
                quantity: qty,
                amount: amount,
                reason: document.getElementById('reason').value
            };
        }
    }).then((result) => {

        if (!result.isConfirmed) return;

        fetch("<?php echo e(route('purchase-returns.store')); ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
            },
            body: JSON.stringify(result.value)
        })
        .then(res => res.json())
        .then(res => {

            Swal.fire({
                icon: 'success',
                title: 'Returned!',
                text: res.message || 'Purchase returned successfully'
            });

            returnBtn.closest('tr').remove();

        })
        .catch(() => {
            Swal.fire('Error', 'Something went wrong', 'error');
        });

    });

});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/purchase_returns/index.blade.php ENDPATH**/ ?>