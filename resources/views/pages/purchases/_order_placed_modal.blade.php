{{-- Right after an order is placed: download / share / email it. --}}
@if(session('purchase_invoice_id') && ($savedInvoice = \App\Models\PurchaseInvoice::withOrders()->with('supplier', 'shop', 'items')->find(session('purchase_invoice_id'))))
    @php $savedNumber = \App\Helpers\InvoiceNumber::for($savedInvoice); @endphp
    <div class="modal fade" id="purchaseDoneModal" tabindex="-1" aria-labelledby="purchaseDoneTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseDoneTitle"><i class="bi bi-check-circle-fill text-success me-2"></i>Purchase order placed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Order</span><strong>{{ $savedNumber }}</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Supplier</span><span>{{ $savedInvoice->supplier->name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Shop</span><span>{{ $savedInvoice->shop->name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Units ordered</span><span>{{ number_format($savedInvoice->items->sum('ordered_quantity')) }}</span></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Order value</span><strong>Tsh {{ number_format($savedInvoice->total_amount, 2) }}</strong></div>

                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        This order is pending. Stock is added when you receive it and confirm what arrived.
                    </div>

                    <div class="alert {{ session('purchase_invoice_emailed') ? 'alert-success' : 'alert-warning' }} small py-2">
                        <i class="bi {{ session('purchase_invoice_emailed') ? 'bi-envelope-check' : 'bi-envelope-exclamation' }} me-1"></i>
                        {{ session('purchase_invoice_email_note') }}
                    </div>

                    @include('pages.purchases._invoice_share', ['invoice' => $savedInvoice])
                </div>
                <div class="modal-footer">
                    <a href="{{ route('purchases.history', $savedInvoice->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> View order</a>
                    @if(auth()->user()->hasPermission('purchases.receive'))
                        <a href="{{ route('purchases.receive', $savedInvoice->id) }}" class="btn btn-sm btn-success"><i class="bi bi-box-arrow-in-down"></i> Receive now</a>
                    @endif
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('purchaseDoneModal')).show();
    });
    </script>
@endif
