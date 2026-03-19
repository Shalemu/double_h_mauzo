<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - DOUBLE H COSMETICS Admin Panel</title>

    @include('components.header')

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

        @keyframes spin{
            0%{transform:rotate(0deg);}
            100%{transform:rotate(360deg);}
        }
    </style>
</head>

<body>

{{-- Top Menu --}}
@include('components.mainmenu')

{{-- Page Content --}}
<main class="container-fluid" style="margin-top:70px;">
    @yield('content')
</main>

{{-- GLOBAL LOADER --}}
<!-- <div id="global-loader">
    <div class="loader-content">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>
</div> -->

{{-- Scripts --}}
@stack('scripts')

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

    if (!product) return;

    // Auto-fill prices
    product.addEventListener('change', () => {
        const opt = product.options[product.selectedIndex];
        buy.value = opt.getAttribute('data-buy') || '';
        sell.value = opt.getAttribute('data-sell') || '';
    });

    // Add item
    const addBtn = formContainer.querySelector('#addItemBtn');
    addBtn.addEventListener('click', () => {
        if (!product.value || !qty.value || !buy.value) {
            alert('Please fill all item fields.');
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

    // Remove item
    table.addEventListener('click', e => {
        if (e.target.classList.contains('remove-item')) {
            const index = e.target.dataset.index;
            items.splice(index, 1);
            renderItems();
        }
    });

    // Payment type
    paymentType.addEventListener('change', () => {
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

    // Paid input change
    paidInput?.addEventListener('input', updateRemainingCredit);

    function updateRemainingCredit() {
        const total = parseFloat(totalEl.innerText) || 0;
        const paid = parseFloat(paidInput?.value || 0);
        const remaining = Math.max(0, total - paid);

        remainingEl.innerText = `Remaining Credit: ${remaining.toFixed(2)}`;
        remainingInput.value = remaining.toFixed(2); // store for Laravel
    }

    // Ensure items exist before submitting
    formContainer.querySelector('form').addEventListener('submit', e => {
        if (items.length === 0) {
            e.preventDefault();
            alert('Add at least one item before submitting!');
        }
    });
}
document.addEventListener('DOMContentLoaded', initPurchaseForm);
</script>

</body>
</html>