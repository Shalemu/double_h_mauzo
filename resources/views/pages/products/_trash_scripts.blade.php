<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.btn-restore').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            Swal.fire({
                title: 'Restore this product?',
                text: 'It will be added back to the active product list.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(`/deleted-products/restore/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Restored!', data.success, 'success');
                        document.getElementById(`trash-row-${id}`)?.remove();
                    } else {
                        Swal.fire('Error', data.error ?? 'Something went wrong.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Something went wrong!', 'error'));
            });
        });
    });

    document.querySelectorAll('.btn-force-delete').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            Swal.fire({
                title: 'Delete permanently?',
                text: 'This cannot be undone — the product will be gone for good.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete permanently!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(`/deleted-products/force-delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', data.success, 'success');
                        document.getElementById(`trash-row-${id}`)?.remove();
                    } else {
                        Swal.fire('Error', data.error ?? 'Something went wrong.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Something went wrong!', 'error'));
            });
        });
    });

});
</script>
