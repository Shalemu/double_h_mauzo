<script>

    document.querySelectorAll('.btn-group [data-status]').forEach(button => {
    button.addEventListener('click', function () {
        let status = this.dataset.status;

        // Remove active class from all buttons
        document.querySelectorAll('.btn-group [data-status]').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        // Hide all status tables
        document.querySelectorAll('.status-table').forEach(table => table.classList.add('d-none'));

        // Show only the selected table
        let activeTable = document.querySelector(`.status-table[data-status="${status}"]`);
        if (activeTable) {
            activeTable.classList.remove('d-none');
        }
        $('#table-search').trigger('input');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable without default search box
    let table = $('#productTable').DataTable({
        responsive: true,
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        dom: 'rtip' // remove default search input
    });

    // Custom Search Box
    $('#productSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Entries dropdown
    $('#entriesSelect').on('change', function() {
        let val = parseInt($(this).val());
        table.page.len(val > 0 ? val : table.data().length).draw();
    });

    const statusTables = {};
    const emptyMessages = {
        runningOutTable: 'No running out products found',
        finishedTable: 'No finished products found',
        expiredTable: 'No expired products found'
    };
    ['runningOutTable', 'finishedTable', 'expiredTable'].forEach(function(id) {
        statusTables[id] = $('#' + id).DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false,
            autoWidth: false,
            dom: 'rtip',
            language: { emptyTable: emptyMessages[id] }
        });
    });

    $('#table-search').on('input', function() {
        const activeStatus = document.querySelector('.status-table:not(.d-none)');
        const activeTable = activeStatus && activeStatus.querySelector('table');
        if (activeTable && statusTables[activeTable.id]) {
            statusTables[activeTable.id].search(this.value).draw();
        }
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/staff/products/_index_scripts.blade.php ENDPATH**/ ?>