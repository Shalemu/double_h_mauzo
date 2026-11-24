<script>
document.addEventListener('DOMContentLoaded', function () {
    const supplierForm = document.getElementById('supplierForm');

    supplierForm.addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch("{{ route('suppliers.store') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                throw new Error(data.message || (data.errors && Object.values(data.errors)[0][0]) || 'Failed to save supplier');
            }

            // Add the new supplier to the Supplier list(s) on the page and pick it.
            document.querySelectorAll('select[name="supplier_id"]').forEach(function (select) {
                select.add(new Option(data.name, data.id, true, true));
                select.dispatchEvent(new Event('change'));
            });

            notify('success', 'Supplier added', data.name + ' is selected.');

            this.reset();

            let modal = bootstrap.Modal.getInstance(document.getElementById('addSupplierModal'));
            modal.hide();
        })
        .catch(error => {
            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to save supplier'
            });
        });
    });
});
</script>