<script>
$(document).ready(function() {
$('#productTable').DataTable({
    "paging": true,            // Enable pagination
    "lengthChange": true,      // Allow user to change number of items per page
    "pageLength": 10,          // Default rows per page
    "searching": true,         // Enable search box
    "ordering": true,          // Enable sorting
    "info": true,              // Show "Showing 1 to X of Y entries"
    "autoWidth": false,
    "responsive": true         // Make table responsive
});
});

<?php if($globalMode): ?>
document.addEventListener('DOMContentLoaded', function() {
const selectAll = document.getElementById('selectAllProducts');
const selectedCountEl = document.getElementById('selectedCount');
const bulkForm = document.getElementById('bulkAssignForm');
const bulkShopSelect = document.getElementById('bulk_shop_id');
const assignAllFlag = document.getElementById('assign_all_unassigned');

function checkboxes() {
    return document.querySelectorAll('.product-checkbox');
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.product-checkbox:checked').length;
    if (selectedCountEl) selectedCountEl.textContent = checked;
}

if (selectAll) {
    selectAll.addEventListener('change', function() {
        checkboxes().forEach(cb => cb.checked = selectAll.checked);
        updateSelectedCount();
    });
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('product-checkbox')) {
        updateSelectedCount();
    }
});

const assignSelectedBtn = document.getElementById('assignSelectedBtn');
if (assignSelectedBtn) {
    assignSelectedBtn.addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);

        if (!bulkShopSelect.value) {
            alert('Please select a shop first.');
            return;
        }
        if (selectedIds.length === 0) {
            alert('Please select at least one product.');
            return;
        }
        if (!confirm(`Assign ${selectedIds.length} selected product(s) to this shop?`)) {
            return;
        }

        // Clear any previous hidden id inputs, then add the current selection
        bulkForm.querySelectorAll('input[name="product_ids[]"]').forEach(el => el.remove());
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = id;
            bulkForm.appendChild(input);
        });

        assignAllFlag.value = '0';
        bulkForm.submit();
    });
}

const assignAllUnassignedBtn = document.getElementById('assignAllUnassignedBtn');
if (assignAllUnassignedBtn) {
    assignAllUnassignedBtn.addEventListener('click', function() {
        if (!bulkShopSelect.value) {
            alert('Please select a shop first.');
            return;
        }
        if (!confirm('This will assign EVERY unassigned product (across the whole system) to this shop. Continue?')) {
            return;
        }

        bulkForm.querySelectorAll('input[name="product_ids[]"]').forEach(el => el.remove());
        assignAllFlag.value = '1';
        bulkForm.submit();
    });
}
});
<?php endif; ?>


document.addEventListener('DOMContentLoaded', function() {
const deleteButtons = document.querySelectorAll('.btn-delete');

deleteButtons.forEach(button => {
button.addEventListener('click', function() {
    const productId = this.dataset.id;

    Swal.fire({
        title: 'Are you sure?',
        text: "This product will be moved to trash!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, move to trash!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success){
                    Swal.fire(
                        'Moved!',
                        data.success,
                        'success'
                    );

                    // Remove the row from table
                    const row = button.closest('tr');
                    if(row){
                        row.remove();
                    }
                } else if(data.error) {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Something went wrong!', 'error');
            });
        }
    });
});
});
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\products\_index_scripts.blade.php ENDPATH**/ ?>