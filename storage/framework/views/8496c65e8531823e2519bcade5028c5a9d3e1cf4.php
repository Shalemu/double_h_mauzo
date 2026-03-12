<div class="container-fluid mt-4 main-content">

    <!-- FIXED EXPENSES REPORT CARD -->
    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Fixed Expenses Report for <?php echo e($shop->name ?? 'Double H Cosmetics Shop'); ?>

            </h5>

            <button class="btn btn-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#addFixedExpenseModal">

                + Add Fixed Expense
            </button>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <input
                        type="text"
                        id="table-search"
                        class="form-control form-control-sm"
                        placeholder="Search title..."
                        style="width:250px;">
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="fixed-expenses-table">

                    <thead class="table-primary align-middle">
                        <tr>
                            <th style="width:5%">SN</th>
                            <th style="width:40%">Title</th>
                            <th style="width:20%">Amount (TZS)</th>
                            <th style="width:20%">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $fixedExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>

                            <td><?php echo e($expense->title); ?></td>

                            <td><?php echo e(number_format($expense->amount,2)); ?></td>

                            <td>

                                <!-- EDIT BUTTON -->
                                <button
                                    class="btn btn-sm btn-warning edit-btn"
                                    data-id="<?php echo e($expense->id); ?>"
                                    data-title="<?php echo e($expense->title); ?>"
                                    data-amount="<?php echo e($expense->amount); ?>"
                                    data-note="<?php echo e($expense->note); ?>">

                                    Edit
                                </button>

                                <!-- DELETE -->
                                <form
                                    action="<?php echo e(route('fixed-expenses.destroy',$expense->id)); ?>"
                                    method="POST"
                                    style="display:inline-block">

                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this expense?')">

                                        Delete
                                    </button>

                                </form>

                            </td>
                        </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>
                            <td colspan="4" class="text-center">
                                No fixed expenses found
                            </td>
                        </tr>

                        <?php endif; ?>

                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>


<!-- ADD EXPENSE MODAL -->
<div class="modal fade" id="addFixedExpenseModal" tabindex="-1">

    <div class="modal-dialog">
        <div class="modal-content">

            <form action="<?php echo e(route('fixed-expenses.store',$shop->id)); ?>" method="POST" enctype="multipart/form-data">

                <?php echo csrf_field(); ?>

                <div class="modal-header">
                    <h5 class="modal-title">Add New Fixed Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Title</label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>

                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            min="0"
                            step="0.01"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Note</label>

                        <textarea
                            name="note"
                            class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Receipt</label>

                        <input
                            type="file"
                            name="receipt"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf">

                        <small class="text-muted">
                            Allowed: JPG PNG PDF
                        </small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">
                        Add Fixed Expense
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>


<!-- EDIT MODAL -->
<div class="modal fade" id="editFixedExpenseModal" tabindex="-1">

    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editExpenseForm" method="POST">

                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="modal-header">
                    <h5 class="modal-title">Edit Fixed Expense</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">Title</label>

                        <input
                            type="text"
                            id="edit-title"
                            name="title"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Amount</label>

                        <input
                            type="number"
                            id="edit-amount"
                            name="amount"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Note</label>

                        <textarea
                            id="edit-note"
                            name="note"
                            class="form-control"></textarea>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">
                        Update Expense
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>



<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>

document.addEventListener('DOMContentLoaded', function() {

    /* TABLE SEARCH */

    const searchInput = document.getElementById('table-search');
    const tableRows = document.querySelectorAll('#fixed-expenses-table tbody tr');

    searchInput.addEventListener('input', function(){

        const query = this.value.toLowerCase();

        tableRows.forEach(row => {

            const title = row.cells[1].textContent.toLowerCase();

            row.style.display = title.includes(query) ? '' : 'none';

        });

    });


    /* EDIT MODAL */

    const editButtons = document.querySelectorAll('.edit-btn');

    editButtons.forEach(button => {

        button.addEventListener('click', function(){

            const id = this.dataset.id;
            const title = this.dataset.title;
            const amount = this.dataset.amount;
            const note = this.dataset.note;

            document.getElementById('edit-title').value = title;
            document.getElementById('edit-amount').value = amount;
            document.getElementById('edit-note').value = note ?? '';

            const form = document.getElementById('editExpenseForm');

            form.action = "/fixed-expenses/" + id;

            const modal = new bootstrap.Modal(document.getElementById('editFixedExpenseModal'));

            modal.show();

        });

    });

});


</script>


<?php if(session('success')): ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

    Swal.fire({

        icon: 'success',
        title: 'Success',
        text: "<?php echo e(session('success')); ?>",
        timer: 2000,
        showConfirmButton: false

    });

});

</script>

<?php endif; ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/fixed_expenses/index.blade.php ENDPATH**/ ?>