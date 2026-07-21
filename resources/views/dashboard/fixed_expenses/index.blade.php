<div class="container-fluid mt-4">

    <!-- FIXED EXPENSES REPORT CARD -->
    <div class="card shadow-sm w-100">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Fixed Expenses Report for {{ $shop->name ?? 'Double H Cosmetics Shop' }}
            </h5>

            <button class="btn btn-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#addFixedExpenseModal">

                + Add Fixed Expense
            </button>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <input
                        type="text"
                        id="table-search"
                        class="form-control form-control-sm"
                        placeholder="Search title..."
                        style="width:250px;">
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="fixed-expenses-table">

                    <thead class="table-primary align-middle">
                        <tr>
                            <th style="width:5%">SN</th>
                            <th style="width:40%">Title</th>
                            <th style="width:20%">Amount (TZS)</th>
                            <th style="width:20%">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($fixedExpenses as $expense)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $expense->title }}</td>

                            <td>{{ number_format($expense->amount,2) }}</td>

                            <td>

                                <!-- EDIT BUTTON -->
                                <button
                                    class="btn btn-sm btn-warning edit-btn"
                                    data-id="{{ $expense->id }}"
                                    data-title="{{ $expense->title }}"
                                    data-amount="{{ $expense->amount }}"
                                    data-note="{{ $expense->note }}">

                                    Edit
                                </button>

                                <!-- DELETE -->
                                <form
                                    action="{{ route('fixed-expenses.destroy',$expense->id) }}"
                                    method="POST"
                                    style="display:inline-block">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger delete-expense-btn">
                                        Delete
                                    </button>

                                </form>

                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="4" class="text-center">
                                No fixed expenses found
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>


<!-- ADD EXPENSE MODAL -->
<div class="modal fade" id="addFixedExpenseModal" tabindex="-1">

    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('fixed-expenses.store',$shop->id) }}" method="POST" enctype="multipart/form-data">

                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add New Fixed Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Title</label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>

                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            min="0"
                            step="0.01"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Note</label>

                        <textarea
                            name="note"
                            class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Receipt</label>

                        <input
                            type="file"
                            name="receipt"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf">

                        <small class="text-muted">
                            Allowed: JPG PNG PDF
                        </small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">
                        Add Fixed Expense
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>


<!-- EDIT MODAL -->
<div class="modal fade" id="editFixedExpenseModal" tabindex="-1">

    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editExpenseForm" method="POST">

                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Fixed Expense</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">Title</label>

                        <input
                            type="text"
                            id="edit-title"
                            name="title"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Amount</label>

                        <input
                            type="number"
                            id="edit-amount"
                            name="amount"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Note</label>

                        <textarea
                            id="edit-note"
                            name="note"
                            class="form-control"></textarea>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">
                        Update Expense
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>



<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Delete confirmation
    document.querySelectorAll('.delete-expense-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const form = this.closest('form');

            Swal.fire({
                title: 'Delete Expense?',
                text: "This expense will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

});
</script>

@if(session('success'))
<script>
document.addEventListener("DOMContentLoaded", function(){
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false
    });
});
</script>
@endif