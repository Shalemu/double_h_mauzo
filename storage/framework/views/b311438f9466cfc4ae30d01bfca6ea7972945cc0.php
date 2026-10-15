<?php $__env->startSection('title', 'Suppliers'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="cat__core__widget p-3 h-100 bg-white">

                    
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <input type="text"
                               id="supplierSearch"
                               class="form-control form-control-sm w-25"
                               placeholder="Search supplier...">

                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                            <i class="bi bi-person-plus"></i> Add Supplier
                        </button>
                    </div>

                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center supplier-table">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($index + 1); ?></td>
                                        <td class="fw-bold"><?php echo e($supplier->name); ?></td>
                                        <td><?php echo e($supplier->phone ?? '-'); ?></td>
                                        <td><?php echo e($supplier->email ?? '-'); ?></td>
                                        <td><?php echo e($supplier->address ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5">No suppliers found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
</div>


<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierModalLabel">
                    <i class="bi bi-person-plus me-2"></i> Add New Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="supplierForm" method="POST" action="<?php echo e(route('suppliers.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter supplier name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Supplier phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Supplier email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Supplier address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.getElementById('supplierSearch').addEventListener('keyup', function () {
    let filter = this.value.toUpperCase();
    document.querySelectorAll('.supplier-table tbody tr').forEach(row => {
        row.style.display =
            row.textContent.toUpperCase().includes(filter) ? '' : 'none';
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const supplierForm = document.getElementById('supplierForm');

    supplierForm.addEventListener('submit', function (e) {
        e.preventDefault();

        fetch("<?php echo e(route('suppliers.store')); ?>", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(supplierForm)
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                throw new Error(data.message || (data.errors && Object.values(data.errors)[0][0]) || 'Failed to save supplier');
            }

            let modal = bootstrap.Modal.getInstance(document.getElementById('addSupplierModal'));
            if (modal) modal.hide();

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Supplier added successfully'
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Failed to save supplier'
            });
        });
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\suppliers\index.blade.php ENDPATH**/ ?>