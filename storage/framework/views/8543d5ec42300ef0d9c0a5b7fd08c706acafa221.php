

<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<div class="cat__content">
    <div class="container-fluid">
        <div class="row g-4" style="padding-left:30px; padding-right:30px;">
            <div class="col-xl-10 mx-auto">
                <div class="cat__core__widget p-3 h-100" style="background:#fff;">

                    <!-- Top Bar: Search + Export | Back + Add Shop -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

                        <!-- Left side -->
                        <div class="d-flex align-items-center mb-2">
                            <div style="margin-right:8px;">
                                <input type="text" id="shopSearch" class="form-control form-control-sm" placeholder="Search shops...">
                            </div>
                            <div style="margin-right:8px;">
                                <button class="btn btn-sm btn-success">Export Excel</button>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-danger">Export PDF</button>
                            </div>
                        </div>

                        <!-- Right side -->
                        <div class="d-flex align-items-center mb-2">
                            <div style="margin-right:8px;">
                                <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary btn-sm">Back</a>
                            </div>
                            <div>
                                <button class="btn btn-primary btn-sm" id="toggleAddShop">Add Shop</button>
                            </div>
                        </div>

                    </div>

<!-- Add Shop Form (hidden by default) -->
<div id="addShopForm" class="mb-3" 
     <?php if(!(session('success') || $errors->any())): ?> style="display:none;" <?php endif; ?>>

    <!-- Success / Error Messages -->
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Add Shop Form -->
    <form method="POST" action="<?php echo e(route('shops.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="Shop Name" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="location" class="form-control" placeholder="Location" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-sm">Save Shop</button>
    </form>
</div>


                    <!-- Shop Tabs (can be empty for now if you don't have dynamic tabs) -->
                    <ul class="nav nav-tabs mb-3 justify-content-center" id="shopTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="shop1-tab" data-bs-toggle="tab" href="#shop1" role="tab">Shops</a>
                        </li>
                    </ul>

                    <!-- Tab Content (Tables) -->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="shop1" role="tabpanel">
<table class="table table-bordered text-center w-75 mx-auto text-uppercase shop-table">
    <thead class="table-warning">
        <tr>
            <th>Name</th>
            <th>Employee</th>
            <th>Total Wages (TZS)</th>
            <th>Capital (TZS)</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalCapital = 0; ?>

        <?php if(isset($shops) && $shops->count() > 0): ?>
            <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <!-- Clickable link to shop details -->
                        <a href="<?php echo e(route('shops.show', $shop->id)); ?>" class="text-decoration-none">
                            <?php echo e($shop->name); ?>

                        </a>
                    </td>
                    <td><?php echo e($shop->staff->count()); ?></td>
                    <td><?php echo e(number_format($shop->staff->sum('wages'))); ?></td>
                    <td><?php echo e(number_format($shop->capital)); ?></td>
                    <td><?php echo e($shop->location); ?></td>
                </tr>

                <?php $totalCapital += $shop->capital; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No shops found.</td>
            </tr>
        <?php endif; ?>

        <tr class="table-success">
            <td colspan="3"><strong>Total Capital</strong></td>
            <td><strong><?php echo e(number_format($totalCapital)); ?></strong></td>
            <td></td>
        </tr>
    </tbody>
</table>



                        </div>
                    </div> <!-- tab-content -->

                </div> <!-- widget -->
            </div>
        </div>
    </div>
</div>

<!-- JS: Toggle form and live search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Add Shop form
    const toggleBtn = document.getElementById('toggleAddShop');
    const addForm = document.getElementById('addShopForm');

    toggleBtn.addEventListener('click', function() {
        if(addForm.style.display === 'none' || addForm.style.display === '') {
            addForm.style.display = 'block';
        } else {
            addForm.style.display = 'none';
        }
    });

    // Live search for shop name
    const searchInput = document.getElementById('shopSearch');
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toUpperCase();
        document.querySelectorAll('.shop-table tbody tr').forEach(tr => {
            if(tr.classList.contains('table-success')) return;
            const nameCell = tr.querySelector('td:first-child');
            if(nameCell && nameCell.textContent.toUpperCase().indexOf(filter) > -1) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    });
});
</script>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/products/show.blade.php ENDPATH**/ ?>