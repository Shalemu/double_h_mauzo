<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title'); ?> - DOUBLE H COSMETICS Admin Panel</title>

    <?php echo $__env->make('components.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <style>
        /* GLOBAL LOADER */
        #global-loader{
            position: fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background: rgba(255,255,255,0.85);
            display:none;
            justify-content:center;
            align-items:center;
            z-index:99999;
        }

        .loader-content{
            text-align:center;
            font-weight:600;
            color:#333;
        }

        .spinner{
            width:60px;
            height:60px;
            border:6px solid #e3e3e3;
            border-top:6px solid #007bff;
            border-radius:50%;
            animation:spin 1s linear infinite;
            margin:auto;
        }

        @keyframes  spin{
            0%{transform:rotate(0deg);}
            100%{transform:rotate(360deg);}
        }
    </style>
</head>

<body>


<?php echo $__env->make('components.mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<main class="container-fluid" style="margin-top:70px;">
    <?php echo $__env->yieldContent('content'); ?>
</main>


<!-- <div id="global-loader">
    <div class="loader-content">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>
</div> -->


<?php echo $__env->yieldPushContent('scripts'); ?>

<script>
document.addEventListener("DOMContentLoaded", function(){

    function showLoader(){
        const loader = document.getElementById('global-loader');
        if(loader) loader.style.display = 'flex';
    }

    function hideLoader(){
        const loader = document.getElementById('global-loader');
        if(loader) loader.style.display = 'none';
    }

    /* SHOW SPINNER ON PAGE NAVIGATION */
    document.querySelectorAll('a').forEach(link=>{
        link.addEventListener('click',function(){
            let href = this.getAttribute('href');

            if(href && !href.startsWith('#') && !href.startsWith('javascript')){
                showLoader();
            }
        });
    });

    /* SHOW SPINNER ON FORM SUBMIT */
    document.querySelectorAll('form').forEach(form=>{
        form.addEventListener('submit',function(){
            showLoader();
        });
    });

    /* AJAX SUPPORT (jQuery) */
    if(window.jQuery){
        $(document).ajaxStart(function(){
            showLoader();
        });

        $(document).ajaxStop(function(){
            hideLoader();
        });
    }

});

</script>

<script>
function initPurchaseForm() {
    const formContainer = document.getElementById('add-purchase-container');
    if (!formContainer) return;

    let items = [];

    const product = formContainer.querySelector('#product');
    const qty = formContainer.querySelector('#quantity');
    const buy = formContainer.querySelector('#purchase_price');
    const sell = formContainer.querySelector('#selling_price');
    const table = formContainer.querySelector('#itemsTable tbody');
    const totalEl = formContainer.querySelector('#grandTotal');
    const itemsInput = formContainer.querySelector('#itemsInput');
    const paymentType = formContainer.querySelector('#paymentType');
    const amountBox = formContainer.querySelector('#amountPaidBox');
    const paidInput = formContainer.querySelector('#amountPaid');
    const remainingEl = formContainer.querySelector('#remainingCredit');
    const remainingInput = formContainer.querySelector('#remainingCreditInput');
    const saleType = formContainer.querySelector('#saleType');
    const addBtn = formContainer.querySelector('#addItemBtn');

    if (!product || !addBtn) return;

    product.addEventListener('change', () => {
        const opt = product.options[product.selectedIndex];
        buy.value = opt.getAttribute('data-buy') || '';
        sell.value = opt.getAttribute('data-sell') || '';
    });

    addBtn.addEventListener('click', () => {
        if (!product.value || !qty.value || !buy.value) {
            Swal.fire('Error', 'Please fill all item fields.', 'error');
            return;
        }

        items.push({
            product_id: product.value,
            name: product.options[product.selectedIndex].text,
            quantity: parseFloat(qty.value),
            price: parseFloat(buy.value),
            selling_price: parseFloat(sell.value || 0),
            sale_type: saleType.value
        });

        renderItems();

        product.value = '';
        qty.value = '';
        buy.value = '';
        sell.value = '';
    });

    function renderItems() {
        table.innerHTML = '';
        let total = 0;

        items.forEach((i, index) => {
            const rowTotal = i.quantity * i.price;
            total += rowTotal;

            table.innerHTML += `
                <tr>
                    <td>${i.name}</td>
                    <td>${i.quantity}</td>
                    <td>${i.price}</td>
                    <td>${rowTotal}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item" data-index="${index}">X</button>
                    </td>
                </tr>
            `;
        });

        totalEl.innerText = total.toFixed(2);
        itemsInput.value = JSON.stringify(items);

        updateRemainingCredit();
    }

    table.addEventListener('click', e => {
        if (e.target.classList.contains('remove-item')) {
            items.splice(e.target.dataset.index, 1);
            renderItems();
        }
    });

    function updateRemainingCredit() {
        const total = parseFloat(totalEl.innerText) || 0;
        const paid = parseFloat(paidInput?.value || 0);
        const remaining = Math.max(0, total - paid);

        remainingEl.innerText = `Remaining Credit: ${remaining.toFixed(2)}`;

        if (remainingInput) {
            remainingInput.value = remaining.toFixed(2);
        }
    }

    paymentType?.addEventListener('change', () => {
        const total = parseFloat(totalEl.innerText) || 0;

        if (paymentType.value === 'credit') {
            amountBox.style.display = 'block';
            paidInput.value = 0;
        } else {
            amountBox.style.display = 'none';
            paidInput.value = total;
        }

        updateRemainingCredit();
    });

    paidInput?.addEventListener('input', updateRemainingCredit);

    formContainer.querySelector('form')?.addEventListener('submit', e => {
        if (items.length === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Add at least one item.', 'error');
            return;
        }

        itemsInput.value = JSON.stringify(items);
    });
}




function initNewProductForm() {
    const form = document.getElementById('newProductForm');
    if (!form) return;

    const paymentType = form.querySelector('#paymentType');
    const amountBox = form.querySelector('#amountPaidBox');
    const paidInput = form.querySelector('#amountPaid');
    const remainingEl = form.querySelector('#remainingCredit');
    const submitBtn = form.querySelector('#submitNewProduct');

    const purchasePrice = form.querySelector('input[name="purchase_price"]');
    const quantity = form.querySelector('input[name="quantity"]');

    // Update remaining credit
    function updateRemainingCredit() {
        const qty = parseFloat(quantity.value || 0);
        const price = parseFloat(purchasePrice.value || 0);
        const paid = parseFloat(paidInput.value || 0);
        const total = qty * price;
        const remaining = Math.max(0, total - paid);
        remainingEl.innerText = `Remaining Credit: ${remaining.toFixed(2)}`;
    }

    // Show/hide deposit box
    paymentType.addEventListener('change', function () {
        if (this.value === 'credit') {
            amountBox.style.display = 'block';
            paidInput.value = 0;
        } else {
            amountBox.style.display = 'none';
            paidInput.value = '';
        }
        updateRemainingCredit();
    });

    paidInput.addEventListener('input', updateRemainingCredit);
    purchasePrice.addEventListener('input', updateRemainingCredit);
    quantity.addEventListener('input', updateRemainingCredit);

    // Cancel button
    const cancelBtn = form.querySelector('#cancelNewProduct');
    cancelBtn.addEventListener('click', () => {
        document.getElementById('newProductContainer').style.display = 'none';
        document.getElementById('add-purchase-container').style.display = 'block';
    });

    // Submit button
    submitBtn.addEventListener('click', async function () {
        const shopId = form.querySelector('select[name="shop_id"]').value;
        const name = form.querySelector('input[name="name"]').value.trim();
        const qty = parseFloat(quantity.value);
        const price = parseFloat(purchasePrice.value);
        const supplier = form.querySelector('select[name="supplier_id"]').value;
        const saleType = form.querySelector('#saleType').value;
        const payment = paymentType.value;

        if (!shopId || !name || !qty || !price || !supplier || !payment || !saleType) {
            Swal.fire('Validation Error', 'Please fill all required fields', 'warning');
            return;
        }

        // Prepare FormData
        const formData = new FormData(form);

        try {
            const res = await fetch("<?php echo e(route('purchases.store_new_product')); ?>", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();

            if (!res.ok) throw data;

            Swal.fire('Success', data.success || 'Product purchased successfully', 'success');

            // Reset form
            form.reset();
            amountBox.style.display = 'none';
            remainingEl.innerText = 'Remaining Credit: 0.00';

            document.getElementById('newProductContainer').style.display = 'none';
            document.getElementById('add-purchase-container').style.display = 'block';
        } catch (error) {
            console.error('AJAX Error:', error);

            if (error.errors) {
                // Laravel validation errors
                const messages = Object.values(error.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', title: 'Validation Error', html: messages });
            } else if (error.message) {
                Swal.fire('Error', error.message, 'error');
            } else {
                Swal.fire('Error', 'Failed to save product', 'error');
            }
        }
    });
}
</script>


<script>
document.addEventListener('click', function (e) {


    // =========================
    // OPEN RETURN MODAL
    // =========================
const returnBtn = e.target.closest('.return-sale-btn');
if (returnBtn) {

    const modalEl = document.getElementById('returnSaleModal');

    if (!modalEl) {
        console.error('Modal not found');
        return;
    }

    const saleIdEl = document.getElementById('returnSaleId');
    const productIdEl = document.getElementById('returnProductId');
    const productNameEl = document.getElementById('returnProductName');
    const qtyEl = document.getElementById('returnQuantity');
    const amountEl = document.getElementById('returnAmount');
    const typeEl = document.getElementById('returnSaleType');

    if (saleIdEl) saleIdEl.value = returnBtn.dataset.saleId || '';
    if (productIdEl) productIdEl.value = returnBtn.dataset.productId || '';
    if (productNameEl) productNameEl.value = returnBtn.dataset.productName || '';
    if (qtyEl) qtyEl.value = 1;
    if (qtyEl) qtyEl.max = returnBtn.dataset.quantity || 1;
    if (amountEl) amountEl.value = returnBtn.dataset.price || '';
    if (typeEl) typeEl.value = returnBtn.dataset.saleType || 'retail';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    return;
}

});
</script>




</body>
</html><?php /**PATH E:\PROJECT\double h\double h\resources\views/main.blade.php ENDPATH**/ ?>