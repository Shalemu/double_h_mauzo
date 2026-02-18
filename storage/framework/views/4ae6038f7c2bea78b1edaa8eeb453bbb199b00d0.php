<?php $__env->startSection('title', 'Dashboard'); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid mt-4 main-content">

    <!-- EXPENSES REPORT CARD -->
    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Expenses Report for <?php echo e($shop->name ?? 'Double H Cosmetics Shop'); ?>

            </h5>

            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                + Add Expense
            </button>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <input type="text" id="table-search" class="form-control form-control-sm"
                           placeholder="Search date..." style="width: 250px;">
                </div>
            </div>

            <!-- EXPENSES TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="expenses-table">
                    <thead class="table-primary align-middle">
                        <tr>
                            <th style="width:5%;">SN</th>
                            <th style="width:30%;">Date</th>
                            <th style="width:20%;">Total (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $expensesByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $expenseData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td>
                                <a href="javascript:void(0)" class="view-date"
                                   data-url="<?php echo e(route('staff.expenses.detail', ['shop' => $shop->id, 'date' => $expenseData['date']])); ?>">
                                    <?php echo e($expenseData['date']); ?>

                                </a>
                            </td>
                            <td><?php echo e(number_format($expenseData['total'], 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="text-center">No expenses found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- DETAILED EXPENSE SECTION -->
            <div id="expense-details" class="mt-4"></div>

        </div>
    </div>
</div>

<!-- ADD EXPENSE MODAL -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true" style="margin-top: 150px;">
    <div class="modal-dialog">
        <div class="modal-content">

         <form action="<?php echo e(route('staff.expenses.store', $shop->id)); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div class="modal-header">
                <h5 class="modal-title">Add New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea class="form-control" name="note"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Receipt</label>
                    <input type="file" class="form-control" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Add Expense</button>
            </div>

         </form>

        </div>
    </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // 🔎 Search
    const searchInput = document.getElementById('table-search');
    const rows = document.querySelectorAll('#expenses-table tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // 📅 View expenses by date (AJAX)
    document.querySelectorAll('.view-date').forEach(link => {
        link.addEventListener('click', function() {
            const url = this.dataset.url;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('expense-details').innerHTML = html;
                })
                .catch(() => alert('Failed to load expenses.'));
        });
    });
});
</script>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/expenses/index.blade.php ENDPATH**/ ?>