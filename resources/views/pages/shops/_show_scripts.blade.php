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
    });
});

</script>
