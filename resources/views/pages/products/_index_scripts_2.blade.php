<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-product-btn');
    const editForm = document.getElementById('editProductForm');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
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
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        });
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
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                // Close modal
                const modalEl = document.getElementById('editProductModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                // Optionally: update table row without reloading
                const row = document.querySelector(`#productTable tr[data-id='${id}']`);
                if(row){
                    row.querySelector('td:nth-child(2)').innerText = data.product.name;
                    row.querySelector('td:nth-child(3)').innerText = data.product.quantity ?? 0;
                    row.querySelector('td:nth-child(4)').innerText = data.product.unit_name ?? '-';
                    row.querySelector('td:nth-child(5)').innerText = data.product.purchase_price ?? '-';
                    row.querySelector('td:nth-child(6)').innerText = data.product.selling_price ?? '-';
                    row.querySelector('td:nth-child(7)').innerText = data.product.wholesale_price ?? '-';
                    row.querySelector('td:nth-child(8)').innerText = data.product.expire_date ?? '-';
                    if(data.product.image){
                        row.querySelector('td:nth-child(1) img').src = data.product.image;
                    }
                }

                // Show success message
                alert(data.success);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update product.');
        });
    });
});
</script>
