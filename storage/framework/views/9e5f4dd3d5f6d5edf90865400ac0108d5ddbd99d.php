
<?php $__env->startSection('title', 'Customer Details'); ?>

<?php echo $__env->make('components/staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid mt-4" style="margin-top: 30px;">

    <!-- Back Button -->
    <br><br><a href="<?php echo e(route('staff.customers.index')); ?>" class="btn btn-secondary btn-sm mb-3">
        <i class="fa fa-arrow-left"></i> Back to Customers
    </a>

    <!-- Customer Profile Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0"><?php echo e($customer->name); ?></h5>
            <?php if($totalDebt > 0): ?>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="fa fa-credit-card"></i> Reduce Debt
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <p><strong>Phone:</strong> <?php echo e($customer->phone ?? '-'); ?></p>
            <p><strong>Email:</strong> <?php echo e($customer->email ?? '-'); ?></p>
            <p><strong>Address:</strong> <?php echo e($customer->address ?? '-'); ?></p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Total Purchases</h6>
                    <h4 class="text-success"><?php echo e(number_format($totalPurchases ?? 0)); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Total Paid</h6>
                    <h4 class="text-primary" id="totalPaid"><?php echo e(number_format($totalPaid ?? 0)); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Total Debt</h6>
                   <h4 class="text-danger" id="totalDebt"><?php echo e(number_format($totalDebt ?? 0)); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase History Table -->
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">Purchase History</h6>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Debt</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($sale->created_at)->format('Y-m-d')); ?></td>
                            <td><?php echo e(number_format($sale->total)); ?></td>
                            <td><?php echo e(number_format($sale->received_amount)); ?></td>
                            <td><?php echo e(number_format($sale->remaining_amount)); ?></td>
                            <td>
                            <?php if($sale->remaining_amount > 0): ?>
                                <span class="badge bg-danger">Debt</span>
                            <?php else: ?>
                                <span class="badge bg-success">Paid</span>
                            <?php endif; ?>
                        </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center">No purchases found for this customer.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentLabel" aria-hidden="true" style="margin-top: 150px;">
  <div class="modal-dialog">
    <form id="recordPaymentForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="customer_id" value="<?php echo e($customer->id); ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentLabel">Record Payment for <?php echo e($customer->name); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>Current Debt: <strong id="currentDebt"><?php echo e(number_format($totalDebt ?? 0)); ?></strong></p>
                <div class="mb-3">
                    <label for="amountPaid" class="form-label">Amount Paid</label>
                    <input type="number" class="form-control" name="amount_paid" id="amountPaid" min="0" max="<?php echo e($totalDebt ?? 0); ?>" required>
                </div>
                <p>Remaining Debt after payment: <strong id="remainingDebt"><?php echo e(number_format($totalDebt ?? 0)); ?></strong></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">Submit Payment</button>
            </div>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amountPaid');
    const remainingDebtEl = document.getElementById('remainingDebt');

    // Correct Blade -> JS conversion
    const totalDebt = Number("<?php echo e($totalDebt ?? 0); ?>");

    function updateRemaining() {
        let paid = parseFloat(amountInput.value) || 0;
        let remaining = Math.max(totalDebt - paid, 0);
        remainingDebtEl.textContent = remaining.toLocaleString();
    }

    amountInput.addEventListener('input', updateRemaining);

    const form = document.getElementById('recordPaymentForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        let formData = new FormData(form);

        try {
            const response = await fetch("<?php echo e(route('staff.customers.recordPayment', $customer)); ?>", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': formData.get('_token') },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                document.getElementById('totalDebt').textContent = Number(data.remaining_debt).toLocaleString();
                document.getElementById('totalPaid').textContent = Number(data.total_paid).toLocaleString();
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('recordPaymentModal')).hide();
                location.reload(); // refresh table
            } else {
                alert(data.message);
            }
        } catch(err) {
            alert("Error processing payment: " + err.message);
        }
    });
});
</script>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/customers/detail.blade.php ENDPATH**/ ?>