<script>
document.addEventListener('DOMContentLoaded', function() {

    let table = $('#customerTable').DataTable({
        responsive: true,
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        dom: 'rtip',
        language: {
            emptyTable: 'No customers found.'
        }
    });

    $('#customerSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#entriesSelect').on('change', function() {
        let val = parseInt($(this).val());
        table.page.len(val > 0 ? val : table.data().length).draw();
    });

});
</script>
