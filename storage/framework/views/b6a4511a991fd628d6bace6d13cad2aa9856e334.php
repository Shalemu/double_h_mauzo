

<?php $__env->startSection('title', 'Create Order'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('components.staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components.mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid mt-4">
    <section class="card mb-5 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Create New Order</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="<?php echo e(route('staff.orders.store')); ?>">
                <?php echo csrf_field(); ?>

                <!-- Shop -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Shop</label>
                        <input type="text" class="form-control"
                            value="<?php echo e(auth()->guard('staff')->user()->shop->name ?? 'N/A'); ?>" disabled>
                    </div>
                </div>

                <hr>
                <h6>Add Items</h6>

                <!-- Add Item Row -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <select id="product" class="form-select">
                            <option value="">Select Product</option>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($product->id); ?>" 
                                        data-sell="<?php echo e($product->selling_price ?? 0); ?>" 
                                        data-sale-type="<?php echo e($product->sale_type ?? 'retail'); ?>">
                                    <?php echo e($product->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" id="quantity" class="form-control" placeholder="Qty" min="1">
                    </div>

                    <div class="col-md-3">
                        <input type="number" id="selling_price" class="form-control" placeholder="Selling Price">
                    </div>

                    <div class="col-md-2">
                        <select id="sale_type" class="form-select">
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="button" id="addItemBtn" class="btn btn-primary w-100">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Selling Price</th>
                                <th>Total</th>
                                <th>Sale Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Total -->
                <h5 class="text-end mt-3">
                    Total: Tsh <span id="grandTotal">0.00</span>
                </h5>

                <!-- Hidden input -->
                <input type="hidden" name="items" id="itemsInput">

                <div class="text-end mt-4">
                    <button class="btn btn-success">Submit Order</button>
                </div>
            </form>
        </div>
    </section>
</div>

<?php $__env->stopSection(); ?>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const product = document.getElementById('product');
    const qty = document.getElementById('quantity');
    const sellingPrice = document.getElementById('selling_price');
    const saleType = document.getElementById('sale_type');
    const table = document.querySelector('#itemsTable tbody');
    const totalEl = document.getElementById('grandTotal');
    const itemsInput = document.getElementById('itemsInput');
    const addBtn = document.getElementById('addItemBtn');

    if (!product || !sellingPrice || !addBtn) return;

    let items = [];

    // Auto-fill selling price & sale type
    product.addEventListener('change', () => {
        const selected = product.options[product.selectedIndex];
        sellingPrice.value = selected.getAttribute('data-sell') || '';
        saleType.value = selected.getAttribute('data-sale-type') || 'retail';
    });

    // Add item to table
    addBtn.addEventListener('click', () => {
        if (!product.value || !qty.value || !sellingPrice.value || !saleType.value) {
            Swal.fire({
                title: 'Warning!',
                text: 'Please fill all item fields',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        items.push({
            product_id: product.value,
            name: product.options[product.selectedIndex].text,
            quantity: parseFloat(qty.value),
            price: parseFloat(sellingPrice.value),
            sale_type: saleType.value
        });

        renderItems();

        // Reset inputs
        product.value = '';
        qty.value = '';
        sellingPrice.value = '';
        saleType.value = 'retail';
    });

    function renderItems() {
        table.innerHTML = '';
        let total = 0;
        items.forEach((item, index) => {
            const rowTotal = item.quantity * item.price;
            total += rowTotal;
            table.innerHTML += `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.price}</td>
                    <td>${rowTotal.toFixed(2)}</td>
                    <td>${item.sale_type}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item" data-index="${index}">X</button>
                    </td>
                </tr>
            `;
        });

        totalEl.innerText = total.toFixed(2);
        itemsInput.value = JSON.stringify(items);
    }

    // Remove item
    table.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item')) {
            const index = e.target.dataset.index;
            items.splice(index, 1);
            renderItems();
        }
    });

    // Prevent empty submit
    document.querySelector('form').addEventListener('submit', (e) => {
        if (items.length === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Warning!',
                text: 'Add at least one item before submitting!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/orders/create.blade.php ENDPATH**/ ?>