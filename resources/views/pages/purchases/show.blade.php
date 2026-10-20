@extends('main')

@section('title', 'Purchase ' . ($invoice->invoice_number ?: '#' . $invoice->id))

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 2);
    $owed = (float) ($invoice->remaining_amount ?? 0);
    $isCredit = ($invoice->payment_type ?? null) === 'credit';
    $subtotal = $invoice->items->sum(fn ($item) => $item->quantity * $item->purchase_price);
@endphp

<style>
@media print {
    .app-sidebar, .app-header, .app-content-header, .no-print { display:none !important; }
    .app-main, .app-wrapper { margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:0 !important; }
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission('purchase-returns.create') && $invoice->items->sum('quantity') > 0)
                <a href="{{ route('returns.purchases.create', $invoice->id) }}" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-left"></i> Return to supplier</a>
            @endif
            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('invoiceFrame').contentWindow.print()"><i class="bi bi-printer"></i> Print</button>
        </div>
    </div>

    @if(session('error'))<div class="alert alert-danger no-print">{{ session('error') }}</div>@endif

    <div class="card shadow-sm mb-4 no-print">
        <div class="card-body py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="small text-muted"><i class="bi bi-send me-1"></i> Send this invoice to the supplier</span>
            @include('pages.purchases._invoice_share', ['invoice' => $invoice])
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- The invoice exactly as the PDF / email shows it. --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 no-print">
                    <h5 class="mb-0">Invoice {{ \App\Helpers\InvoiceNumber::for($invoice) }}</h5>
                    @if($isCredit && $owed > 0)
                        <span class="badge bg-danger">Still owe Tsh {{ $money($owed) }}</span>
                    @else
                        <span class="badge bg-success">Paid</span>
                    @endif
                </div>
                <div class="card-body p-0 bg-light">
                    <iframe id="invoiceFrame" src="{{ route('purchases.invoice_preview', $invoice->id) }}" title="Invoice preview"
                            style="width:100%; height:900px; border:0; display:block; background:#fff;"
                            onload="try { this.style.height = (this.contentDocument.body.scrollHeight + 40) + 'px'; } catch (e) {}"></iframe>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Date</dt><dd class="col-7">{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y, H:i') }}</dd>
                        <dt class="col-5 text-muted fw-normal">Shop / Store</dt><dd class="col-7">{{ $invoice->shop->name ?? 'N/A' }}</dd>
                        <dt class="col-5 text-muted fw-normal">Supplier</dt>
                        <dd class="col-7">
                            {{ $invoice->supplier->name ?? '—' }}
                            @if($invoice->supplier->phone ?? null)<div class="text-muted">{{ $invoice->supplier->phone }}</div>@endif
                        </dd>
                        <dt class="col-5 text-muted fw-normal">Payment</dt><dd class="col-7 mb-0">{{ $isCredit ? 'Credit' : 'Cash' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Payments to supplier</div>
                <div class="card-body">
                    {{-- Cash purchases are paid at the time of purchase (no separate payment record). --}}
                    @if(!$isCredit && (float) $invoice->amount_paid > 0)
                        <div class="d-flex justify-content-between small {{ $invoice->payments->isNotEmpty() ? 'border-bottom pb-2 mb-2' : '' }}">
                            <span class="text-muted">{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y') }} · Cash at purchase</span>
                            <span class="text-success text-nowrap">{{ $money($invoice->amount_paid) }}</span>
                        </div>
                    @endif
                    @forelse($invoice->payments as $payment)
                        <div class="d-flex justify-content-between small {{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                            <span class="text-muted">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y') }}</span>
                            <span class="text-success text-nowrap">{{ $money($payment->amount) }}</span>
                        </div>
                    @empty
                        @if($isCredit || (float) $invoice->amount_paid <= 0)
                            <p class="text-muted small mb-0">No payments recorded yet.</p>
                        @endif
                    @endforelse

                    @if($isCredit && $owed > 0)
                        <form method="POST" action="{{ route('purchases.deposit') }}" class="d-flex gap-2 mt-3 pt-3 border-top no-print">
                            @csrf
                            <input type="hidden" name="purchase_invoice_id" value="{{ $invoice->id }}">
                            <input type="number" name="amount" class="form-control form-control-sm" min="1" max="{{ $owed }}" step="any" placeholder="Amount (Tsh)" aria-label="Payment amount" required>
                            <button class="btn btn-sm btn-success text-nowrap"><i class="bi bi-cash"></i> Pay</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Returns to supplier</div>
                <div class="card-body">
                    @forelse($returns as $return)
                        <div class="d-flex justify-content-between small {{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                            <div>
                                <div class="fw-semibold">{{ $return->product->name ?? 'Product' }} × {{ $return->quantity }}</div>
                                <div class="text-muted">{{ ($return->returned_at ?? $return->created_at)->format('d M Y') }}{{ $return->reason ? ' · ' . $return->reason : '' }}</div>
                            </div>
                            <div class="text-success text-nowrap">− {{ $money($return->amount) }}</div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No returns on this invoice.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
