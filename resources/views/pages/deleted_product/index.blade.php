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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr id="row-{{ $product->id }}">
                        <td class="text-start">{{ $product->name }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ $product->unit ? $product->unit->name : '-' }}</td>
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
                        <td colspan="4" class="text-center text-muted">No deleted products found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@include('pages.deleted_product._index_styles')

@include('pages.deleted_product._index_scripts')
