
<?php $actionTemplate = route($routeName, '__SHOP__'); ?>

<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-labelledby="<?php echo e($modalId); ?>Title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" enctype="multipart/form-data"
              data-action-template="<?php echo e($actionTemplate); ?>"
              action="<?php echo e($shopId ? str_replace('__SHOP__', $shopId, $actionTemplate) : ''); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="<?php echo e($modalId); ?>Title"><?php echo e($heading); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Shop</label>
                    <select class="form-select expense-shop" required>
                        <option value="">Choose shop...</option>
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($shop->id); ?>" <?php echo e(((string) $shopId === (string) $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount (Tsh)</label>
                    <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control" rows="2"></textarea>
                </div>
                <?php if($withReceipt): ?>
                    <div class="mb-3">
                        <label class="form-label">Receipt (optional, jpg/png/pdf, max 2MB)</label>
                        <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#<?php echo e($modalId); ?> form');
    const shop = form.querySelector('.expense-shop');
    shop.addEventListener('change', function () {
        form.action = shop.value ? form.dataset.actionTemplate.replace('__SHOP__', shop.value) : '';
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\overview\_add_expense_modal.blade.php ENDPATH**/ ?>