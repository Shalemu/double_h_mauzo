<div class="container-fluid mt-4">

    <div class="card shadow-sm p-3 w-100" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-trash me-2"></i>Deleted Products (Trash)</h4>
            <span class="text-muted">Total: {{ $products->count() }} item(s)</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center" style="min-width: 900px;">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Sale Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr id="row-{{ $product->id }}">
                        <td class="text-start">{{ $product->name }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ $product->unit ? $product->unit->name : '-' }}</td>
                        <td>{{ ucfirst($product->sale_type ?? '-') }}</td>
                        <td>
                            <button class="btn btn-sm btn-success btn-restore me-1" data-id="{{ $product->id }}">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button class="btn btn-sm btn-danger btn-force-delete" data-id="{{ $product->id }}">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No deleted products found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .table thead th {
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        font-size: 14px;
    }

    .btn-restore, .btn-force-delete {
        min-width: 110px;
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
        .btn-restore, .btn-force-delete {
            min-width: 90px;
            font-size: 13px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const sendRequest = async (url, method = 'POST') => {
        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
</script>