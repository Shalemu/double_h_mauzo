@extends('main')

@section('title', 'Receive order ' . \App\Helpers\InvoiceNumber::for($invoice))

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 2);
    $orderValue = $invoice->items->sum(fn ($item) => $item->ordered_quantity * $item->purchase_price);
@endphp

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="{{ route('purchases.history', $invoice->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text"></i> View order</a>
    </div>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('purchases.confirm_receive', $invoice->id) }}" id="receiveForm">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0"><i class="bi bi-box-arrow-in-down me-2"></i>Receive order {{ \App\Helpers\InvoiceNumber::for($invoice) }}</h5>
                <span class="text-muted small">
                    {{ $invoice->supplier->name ?? '—' }} → <strong>{{ $invoice->shop->name ?? '—' }}</strong>
                    · ordered {{ optional($invoice->ordered_at)->format('d M Y') }}
                </span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Enter what actually arrived and the actual prices. A line that didn't arrive at all can be set to 0.
                    Confirming adds the stock to <strong>{{ $invoice->shop->name ?? 'the shop' }}</strong> and records the purchase.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0" id="receiveTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Product</th>
                                <th>Ordered</th>
                                <th style="min-width:110px">Received qty</th>
                                <th style="min-width:130px">Buying price (Tsh)</th>
                                <th style="min-width:130px">Retail price (Tsh)</th>
                                <th style="min-width:130px">Wholesale price (Tsh)</th>
                                <th>Line total (Tsh)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                @php
                                    $product = $item->product;
                                    $field = fn ($name, $default) => old("lines.{$item->id}.{$name}", $default);
                                @endphp
                                <tr data-ordered="{{ $item->ordered_quantity }}" data-order-price="{{ $item->purchase_price }}" data-name="{{ $product->name ?? 'Deleted product' }}">
                                    <td class="text-start">
                                        <div class="fw-semibold">{{ $product->name ?? 'Deleted product' }}</div>
                                        <div class="text-muted small">In stock now: {{ number_format($product->quantity ?? 0) }} {{ $product->unit->short_name ?? '' }}</div>
                                    </td>
                                    <td>{{ number_format($item->ordered_quantity) }}</td>
                                    <td>
                                        <input type="number" name="lines[{{ $item->id }}][quantity]" class="form-control form-control-sm text-end js-qty"
                                               min="0" step="1" value="{{ $field('quantity', $item->ordered_quantity) }}" aria-label="Received quantity">
                                        <div class="small js-qty-note"></div>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $item->id }}][purchase_price]" class="form-control form-control-sm text-end js-price"
                                               min="0" step="any" value="{{ $field('purchase_price', $item->purchase_price) }}" aria-label="Buying price">
                                        <div class="small js-price-note"></div>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $item->id }}][selling_price]" class="form-control form-control-sm text-end"
                                               min="0" step="any" value="{{ $field('selling_price', $item->selling_price ?? $product->selling_price ?? '') }}" aria-label="Retail price">
                                        <div class="text-muted small">now {{ $money($product->selling_price ?? 0) }}</div>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $item->id }}][wholesale_price]" class="form-control form-control-sm text-end"
                                               min="0" step="any" value="{{ $field('wholesale_price', $item->wholesale_price ?? $product->wholesale_price ?? '') }}" aria-label="Wholesale price">
                                        <div class="text-muted small">now {{ $money($product->wholesale_price ?? 0) }}</div>
                                    </td>
                                    <td class="text-end js-line-total">{{ $money($item->ordered_quantity * $item->purchase_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th class="text-start" colspan="6">Order value (as ordered)</th>
                                <th class="text-end">{{ $money($orderValue) }}</th>
                            </tr>
                            <tr class="table-success">
                                <th class="text-start" colspan="6">Received value</th>
                                <th class="text-end" id="receivedTotal">{{ $money($orderValue) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Payment to supplier</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="paymentType">Payment type</label>
                        <select name="payment_type" id="paymentType" class="form-select" required>
                            <option value="cash" {{ old('payment_type', $invoice->payment_type) === 'cash' ? 'selected' : '' }}>Cash (paid in full)</option>
                            <option value="credit" {{ old('payment_type', $invoice->payment_type) === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="depositBox">
                        <label class="form-label" for="amountPaid">Deposit paid now (Tsh)</label>
                        <input type="number" name="amount_paid" id="amountPaid" class="form-control" min="0" step="any"
                               value="{{ old('amount_paid', (float) $invoice->amount_paid ?: '') }}">
                        <small class="text-danger" id="owedNote"></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('purchases.index') }}" class="btn btn-light">Not yet</a>
            {{-- type="button": the form is sent from the confirmation pop-up, not straight away. --}}
            <button type="button" class="btn btn-success" id="confirmReceive"><i class="bi bi-check2-circle"></i> Confirm receipt &amp; add to stock</button>
        </div>
    </form>
</div>

@include('pages.purchases._receive_scripts')
@endsection
