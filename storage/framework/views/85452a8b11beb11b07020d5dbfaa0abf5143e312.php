<script>
document.addEventListener('DOMContentLoaded', function() {

    const sendRequest = async (url, method = 'POST') => {
        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            return await res.json();
        } catch(err) {
            console.error(err);
            return { error: 'Request failed' };
        }
    };

    // Restore product
    document.querySelectorAll('.btn-restore').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const result = await Swal.fire({
                title: 'Restore Product?',
                text: 'This product will be restored to inventory.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore!',
                cancelButtonText: 'Cancel'
            });
            if(result.isConfirmed){
                const data = await sendRequest(`/deleted-products/restore/${id}`, 'POST');
                if(data.success){
                    Swal.fire('Restored!', data.success, 'success');
                    document.getElementById(`row-${id}`).remove();
                } else {
                    Swal.fire('Error', data.error || 'Could not restore product', 'error');
                }
            }
        });
    });

    // Force delete product
    document.querySelectorAll('.btn-force-delete').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const result = await Swal.fire({
                title: 'Permanently Delete?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            });
            if(result.isConfirmed){
                const data = await sendRequest(`/deleted-products/force-delete/${id}`, 'DELETE');
                if(data.success){
                    Swal.fire('Deleted!', data.success, 'success');
                    document.getElementById(`row-${id}`).remove();
                } else {
                    Swal.fire('Error', data.error || 'Could not delete product', 'error');
                }
            }
        });
    });

});
</script><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\deleted_product\_index_scripts.blade.php ENDPATH**/ ?>