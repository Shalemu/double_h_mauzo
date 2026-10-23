<script>
document.addEventListener('DOMContentLoaded', function () {

    const showBtn = document.getElementById('show-add-purchase');
    const tableContainer = document.getElementById('purchases-table-container');
    const formContainer = document.getElementById('add-purchase-container');
    const newProductContainer = document.getElementById('newProductContainer');

    // SEARCH
    document.getElementById('purchases-table-search').addEventListener('input', function () {
        let q = this.value.toLowerCase();
        document.querySelectorAll('#purchases-table tbody tr').forEach(row => {
            row.style.display = row.cells[1].innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // VIEW PURCHASE DETAILS
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('view-date')) {
            e.preventDefault();
            fetch(e.target.dataset.url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('purchase-details').innerHTML = html;
                });
        }
    });

    // LOAD PURCHASE FORM
    showBtn.addEventListener('click', function () {
        fetch("<?php echo e(route('purchases.create')); ?>")
            .then(res => res.text())
            .then(html => {
                formContainer.innerHTML = html;
                formContainer.style.display = 'block';
                tableContainer.style.display = 'none';
                showBtn.style.display = 'none';

                // Initialize purchase form functions if exists
                if(typeof initPurchaseForm === 'function') initPurchaseForm();

                // Initialize "New Item" button inside purchase form
                const btnNewItem = document.getElementById('btnNewItem');
                if(btnNewItem){
                    btnNewItem.addEventListener('click', function(e){
                        e.preventDefault();
                        loadNewProductForm();
                    });
                }

                // CANCEL PURCHASE FORM
                const cancelBtn = document.getElementById('cancel-add-purchase');
                if(cancelBtn){
                    cancelBtn.onclick = function () {
                        formContainer.innerHTML = '';
                        formContainer.style.display = 'none';
                        tableContainer.style.display = 'block';
                        showBtn.style.display = 'inline-block';
                        newProductContainer.style.display = 'none';
                        newProductContainer.innerHTML = '';
                    };
                }
            })
            .catch(err => console.error(err));
    });

    // LOAD NEW PRODUCT FORM
function loadNewProductForm() {
    formContainer.style.display = 'none';

    fetch("<?php echo e(route('purchases.new_product')); ?>")
        .then(res => res.text())
        .then(html => {
            newProductContainer.innerHTML = html;
            newProductContainer.style.display = 'block';

            initNewProductForm(); // VERY IMPORTANT
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Failed to load form', 'error');
        });
}

});
</script><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\_index_scripts.blade.php ENDPATH**/ ?>