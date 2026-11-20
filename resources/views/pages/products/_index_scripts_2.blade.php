<script>
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editProductForm');

    // Delegated: DataTables keeps rows of other pages out of the DOM, so bind once on the document.
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.edit-product-btn');
        if (!button) return;
        (function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const brand = this.dataset.brand;
            const category = this.dataset.category;
            const shop = this.dataset.shop;
            const unit = this.dataset.unit;
            const quantity = this.dataset.quantity;
            const purchase = this.dataset.purchase;
            const selling = this.dataset.selling;
            const wholesale = this.dataset.wholesale;
            const expire = this.dataset.expire;
            const image = this.dataset.image;

            // Fill form fields
            editForm.dataset.id = id; // save ID for AJAX
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_barcode').value = this.dataset.barcode || '';
            // Older products may carry a brand typed in before the list existed — keep it selectable.
            const brandSelect = document.getElementById('edit_brand');
            if (brand && !Array.from(brandSelect.options).some(o => o.value === brand)) {
                brandSelect.add(new Option(brand, brand));
            }
            brandSelect.value = brand || '';
            document.getElementById('edit_category_id').value = category;
            const editShopSelect = document.getElementById('edit_shop_id');
            if (editShopSelect) { editShopSelect.value = shop || ''; }
            document.getElementById('edit_unit_id').value = unit;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_purchase_price').value = purchase;
            document.getElementById('edit_selling_price').value = selling;
            document.getElementById('edit_wholesale_price').value = wholesale;
            document.getElementById('edit_expire_date').value = expire;

            const imgPreview = document.getElementById('edit_image_preview');
            if(image){
                imgPreview.src = image;
                imgPreview.style.display = 'block';
            } else {
                imgPreview.style.display = 'none';
            }

            // Show modal
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editProductModal')).show();
        }).call(button);
    });

    // Preview uploaded image
    const imageInput = document.getElementById('edit_image');
    const imagePreview = document.getElementById('edit_image_preview');

    imageInput.addEventListener('change', function() {
        if(this.files && this.files[0]){
            const reader = new FileReader();
            reader.onload = function(e){
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // AJAX submit
    editForm.addEventListener('submit', function(e){
        e.preventDefault();

        const id = this.dataset.id;
        const formData = new FormData(this);

        fetch(`/products/${id}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                // Validation (422) → first field error; anything else → generic message.
                const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                throw new Error(firstError || data.message || 'Failed to update product.');
            }
            return data;
        })
        .then(data => {
            if (!data.success) return;

            bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();

            // Refresh the edit button's stored values so reopening this product shows what was just saved.
            const btn = document.querySelector(`.edit-product-btn[data-id='${id}']`);
            if (btn) {
                const val = (field) => formData.get(field) ?? '';
                btn.dataset.name      = val('name');
                btn.dataset.barcode   = val('barcode');
                btn.dataset.brand     = val('brand');
                btn.dataset.category  = val('category_id');
                btn.dataset.unit      = val('unit_id');
                btn.dataset.quantity  = val('quantity');
                btn.dataset.purchase  = val('purchase_price');
                btn.dataset.selling   = val('selling_price');
                btn.dataset.wholesale = val('wholesale_price');
                btn.dataset.expire    = val('expire_date');
                if (formData.has('shop_id')) btn.dataset.shop = val('shop_id');
                if (data.product.image) btn.dataset.image = data.product.image;
            }

            // Update the table row in place.
            const row = document.querySelector(`#productTable tr[data-id='${id}']`);
            if (row) {
                const setCol = (col, value) => {
                    const cell = row.querySelector(`td[data-col='${col}']`);
                    if (cell) cell.innerText = value ?? '-';
                };
                ['name', 'quantity', 'unit_name', 'purchase_price', 'selling_price', 'wholesale_price', 'expire_date']
                    .forEach(col => setCol(col, data.product[col]));
                const img = row.querySelector("td[data-col='image'] img");
                if (img && data.product.image) img.src = data.product.image;
            }

            imageInput.value = '';
            notify('success', data.success, data.product.name);
        })
        .catch(error => {
            console.error('Error:', error);
            notify('error', 'Update failed', error.message);
        });
    });
});
</script>
