@extends('main')

@section('title', 'Deleted Products')

@section('content')

<div class="dashboard-panel bg-white rounded-3 border p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="bi bi-trash3 me-2"></i>Deleted Products ({{ $trashedProducts->count() }})</h5>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Shop</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Selling Price</th>
                    <th>Barcode</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trashedProducts as $item)
                    <tr id="trash-row-{{ $item->id }}">
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->brand ?? '-' }}</td>
                        <td>{{ $item->shop->name ?? '-' }}</td>
                        <td>{{ $item->category->name ?? '-' }}</td>
                        <td>{{ number_format($item->quantity) }}</td>
                        <td>{{ number_format($item->selling_price, 2) }}</td>
                        <td>{{ $item->barcode ?? '-' }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-success btn-restore" data-id="{{ $item->id }}">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-force-delete" data-id="{{ $item->id }}">
                                <i class="bi bi-x-circle"></i> Delete Permanently
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Trash is empty.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
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
@endpush

@endsection
