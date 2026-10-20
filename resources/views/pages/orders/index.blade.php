{{-- resources/views/dashboard/admin/orders/index.blade.php --}}

<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Orders for Shop: {{ $shop->name ?? 'N/A' }}</h5>
        </div>

        <div class="card-body">

            {{-- Success message --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Items</th>
                            <th>Total (Tsh)</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $index => $order)
                            @php
                                $order->load('staff', 'items.product'); // ensure relations loaded
                                $total = $order->items->sum('total') ?? 0;
                                $count = $order->items->count() ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $order->staff->full_name ?? 'N/A' }}</td>
                                <td>{{ $count }}</td>
                                <td>{{ number_format($total, 2) }}</td>
                                <td>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-success">Approved</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">Unknown</span>
                                    @endswitch
                                </td>
                                <td>{{ optional($order->created_at)->format('d M Y H:i') ?? 'N/A' }}</td>
                                <td>
                                    {{-- View Order Modal --}}
                                    <button 
                                        class="btn btn-sm btn-info view-order-btn"
                                        data-order='@json($order->toArray())'
                                    >
                                        <i class="bi bi-eye"></i> View
                                    </button>

                                    {{-- Approve/Reject --}}
                                    @if($order->status == 'pending')
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>

                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Order Details Modal --}}
                <div class="modal fade" id="orderModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Order Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body" id="orderDetailsContent">
                                {{-- Dynamic content injected here --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.view-order-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const order = JSON.parse(this.getAttribute('data-order'));

            // Format staff name
            const staffName = order.staff?.full_name ?? 'N/A';
            const itemsTotal = order.items.reduce((sum, i) => sum + Number(i.total), 0);
            const totalAmount = Number(order.total_amount) || itemsTotal;

            // Build HTML
            let html = `
                <p><strong>Staff:</strong> ${staffName}</p>
                <p><strong>Status:</strong> ${order.status}</p>
                <p><strong>Total:</strong> Tsh ${totalAmount.toLocaleString()}</p>
                <hr>
                <h6>Items</h6>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            order.items.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.product?.name ?? 'N/A'}</td>
                        <td>${item.quantity}</td>
                        <td>Tsh ${Number(item.price).toLocaleString()}</td>
                        <td>Tsh ${Number(item.total).toLocaleString()}</td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;

            document.getElementById('orderDetailsContent').innerHTML = html;

            new bootstrap.Modal(document.getElementById('orderModal')).show();
        });
    });
});
</script>