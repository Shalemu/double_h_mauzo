

<?php
use Carbon\Carbon;
?>


<div class="container-fluid mt-4 main-content">
    <div class="container-fluid align-items-center" style="max-width: 1300px; margin: 0 auto;">
        <button type="button" class="btn btn-outline-secondary float-end" data-bs-toggle="modal" data-bs-target="#depositModal">
    Deposit
</button>

        <h3 class="mb-4 text-center">Credit Purchases</h3>

      
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-info h-100 text-center">
                    <div class="card-header bg-info text-white">Today</div>
                    <div class="card-body">
                        <h5>Tsh <?php echo e(number_format($dailyCredit ?? 0, 2)); ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-primary h-100 text-center">
                    <div class="card-header bg-primary text-white">This Month</div>
                    <div class="card-body">
                        <h5>Tsh <?php echo e(number_format($monthlyCredit ?? 0, 2)); ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-dark h-100 text-center">
                    <div class="card-header bg-dark text-white">Total Credit</div>
                    <div class="card-body">
                        <h5>Tsh <?php echo e(number_format($totalCredit ?? 0, 2)); ?></h5>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card shadow-sm">
            <div class="card-header bg-white text-center">
                <h5 class="mb-0">All Credit Purchases</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Supplier</th>
                            <th>Shop</th>
                            <th>Total Amount</th>
                            <th>Remaining Credit</th>
                            <th>Purchase Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $creditByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        
                        <tr class="table-secondary">
                            <td colspan="8" class="text-start">
                                <strong><?php echo e($date); ?></strong>
                            </td>
                        </tr>

                         
                        <?php $__currentLoopData = $data['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($invoice->invoice_number ?? '-'); ?></td>
                                <td><?php echo e($invoice->supplier->name ?? '-'); ?></td>
                                <td><?php echo e($invoice->shop->name ?? '-'); ?></td>
                                <td><?php echo e(number_format($invoice->total_amount, 2)); ?></td>
                                <td class="text-danger"><?php echo e(number_format($invoice->remaining_amount, 2)); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($invoice->purchased_at)->format('Y-m-d')); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-history" 
                                            data-url="<?php echo e(route('purchases.history', $invoice->id)); ?>">
                                        History
                                    </button>

                                    <a href="<?php echo e(route('purchases.print', $invoice->id)); ?>" 
                                    target="_blank" class="btn btn-sm btn-secondary">
                                        Print
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">No credit purchases found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo e(route('purchases.deposit')); ?>">
            <?php echo csrf_field(); ?>

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Deposit Credit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Supplier -->
                    <div class="mb-3">
                        <label>Supplier</label>
                        <select id="supplierSelect" class="form-control">
                            <option value="">-- All Suppliers --</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($supplier->id); ?>">
                                    <?php echo e($supplier->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Select Purchase -->
                    <div class="mb-3">
                        <label>Select Invoice</label>
                        <select name="purchase_invoice_id" id="purchaseSelect" class="form-control" required>
                        <option value="">-- Select Invoice --</option>
                        <?php $__currentLoopData = $creditByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $data['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($invoice->id); ?>" data-supplier="<?php echo e($invoice->supplier_id); ?>">
                                    <?php echo e($invoice->invoice_number ?? 'No Invoice'); ?> 
                                    - Remaining: <?php echo e(number_format($invoice->remaining_amount,2)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label>Deposit Amount</label>
                        <input type="number" name="amount" class="form-control" required min="1">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Save Deposit</button>
                </div>
            </div>
        </form>
    </div>
</div>

    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
          
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('supplierSelect').addEventListener('change', function () {
    let supplierId = this.value;
    let options = document.querySelectorAll('#purchaseSelect option');

    options.forEach(option => {
        if (!option.value) return; // skip default

        if (!supplierId || option.dataset.supplier === supplierId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });

    // reset selected value
    document.getElementById('purchaseSelect').value = '';
});

// credit history
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-history').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(this.dataset.url)
                .then(res => res.text())
                .then(html => {
                    const modal = document.getElementById('historyModal');
                    modal.querySelector('.modal-body').innerHTML = html;
                    new bootstrap.Modal(modal).show();
                });
        });
    });
});

</script>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/credit/index.blade.php ENDPATH**/ ?>