<div class="container-fluid mt-4 main-content">

    <!-- FIXED EXPENSES REPORT CARD -->
    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Fixed Expenses Report for <?php echo e($shop->name ?? 'Double H Cosmetics Shop'); ?></h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFixedExpenseModal">
                + Add Fixed Expense
            </button>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <input type="text" id="table-search" class="form-control form-control-sm" placeholder="Search title..." style="width: 250px;">
                </div>
            </div>

            <!-- FIXED EXPENSES TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="fixed-expenses-table">
                    <thead class="table-primary align-middle">
                        <tr>
                            <th style="width:5%;">SN</th>
                            <th style="width:40%;">Title</th>
                            <th style="width:20%;">Amount (TZS)</th>
                            <th style="width:20%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $fixedExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($expense->title); ?></td>
                            <td><?php echo e(number_format($expense->amount, 2)); ?></td>
                            <td>
                                <a href="<?php echo e(route('fixed-expenses.edit', $expense->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form action="<?php echo e(route('fixed-expenses.destroy', $expense->id)); ?>" method="POST" style="display:inline-block;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this fixed expense?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center">No fixed expenses found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- ADD FIXED EXPENSE MODAL -->
<div class="modal fade" id="addFixedExpenseModal" tabindex="-1" aria-hidden="true" style="margin-top: 150px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('fixed-expenses.store', $shop->id)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Fixed Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" name="note"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="receipt" class="form-label">Upload Receipt</label>
                        <input type="file" class="form-control" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Allowed: JPG, PNG, PDF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Fixed Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Table search
    const searchInput = document.getElementById('table-search');
    const table = document.getElementById('fixed-expenses-table');
    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
});
</script>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/fixed_expenses/index.blade.php ENDPATH**/ ?>