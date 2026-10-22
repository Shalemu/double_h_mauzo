@extends('main')

@section('title', 'Return to supplier — Invoice ' . ($invoice->invoice_number ?: '#' . $invoice->id))

@section('content')
@php $money = fn ($v) => number_format((float) $v, 2); @endphp
<div class="container-fluid py-4">
    <a href="{{ route('returns.purchases') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to purchase returns</a>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('returns.purchases.store', $invoice->id) }}" id="returnForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Send items back from invoice {{ $invoice->invoice_number ?: '#' . $invoice->id }}</h5>
                        <small class="text-muted">Enter how many of each item are going back to {{ $invoice->supplier->name ?? 'the supplier' }}. Leave 0 for items you're keeping.</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Product</th>
                                        <th>Bought</th>
                                        <th>Already returned</th>
                                        <th title="Units of this product still in the shop/store">In stock</th>
                                        <th>Can return</th>
                                        <th style="width:130px">Return qty</th>
                                        <th style="width:150px">Credit (Tsh)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $item)
                                        @php
                                            $already = (int) ($returned[$item->id] ?? 0);
                                            $onInvoice = (int) $item->quantity;
                                            $inStock = (int) ($item->product->quantity ?? 0);
                                            // Can't send back more than is left on the invoice or still on the shelf.
                                            $left = max(0, min($onInvoice, $inStock));
                                        @endphp
                                        <tr class="return-line" data-unit="{{ round($item->purchase_price, 2) }}">
                                            <td class="text-start">
                                                {{ $item->product->name ?? 'Deleted product' }}
                                                <div class="small text-muted">Tsh {{ $money($item->purchase_price) }} each</div>
                                            </td>
                                            <td>{{ $onInvoice + $already }}</td>
                                            <td>{{ $already ?: '—' }}</td>
                                            <td class="{{ $inStock < $onInvoice ? 'text-warning fw-semibold' : '' }}">{{ $inStock }}</td>
                                            <td class="fw-semibold">{{ $left }}</td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary qty-step" data-step="-1" aria-label="Less" {{ $left ? '' : 'disabled' }}>−</button>
                                                    <input type="number" name="lines[{{ $item->id }}][quantity]" class="form-control text-center return-qty"
                                                           value="{{ old('lines.' . $item->id . '.quantity', 0) }}" min="0" max="{{ $left }}" {{ $left ? '' : 'disabled' }}
                                                           aria-label="Quantity of {{ $item->product->name ?? 'item' }} to return">
                                                    <button type="button" class="btn btn-outline-secondary qty-step" data-step="1" aria-label="More" {{ $left ? '' : 'disabled' }}>+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="lines[{{ $item->id }}][amount]" class="form-control form-control-sm text-end return-amount"
                                                       value="{{ old('lines.' . $item->id . '.amount') }}" placeholder="0.00" {{ $left ? '' : 'disabled' }}
                                                       aria-label="Credit for {{ $item->product->name ?? 'item' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="small text-muted mt-2 mb-0"><em>Can return</em> is the smaller of what's left on this invoice and what's still in stock.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted fw-normal">Bought on</dt><dd class="col-7">{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y, H:i') }}</dd>
                            <dt class="col-5 text-muted fw-normal">Shop / Store</dt><dd class="col-7">{{ $invoice->shop->name ?? '—' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Supplier</dt><dd class="col-7">{{ $invoice->supplier->name ?? '—' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Invoice total</dt><dd class="col-7">Tsh {{ $money($invoice->total_amount) }}</dd>
                            <dt class="col-5 text-muted fw-normal">Payment</dt>
                            <dd class="col-7 mb-0">
                                @if(($invoice->payment_type ?? null) === 'credit' && $invoice->remaining_amount > 0)
                                    Credit — still owe Tsh {{ $money($invoice->remaining_amount) }}
                                @else
                                    Paid
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold">Why is it going back?</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3" role="radiogroup" aria-label="Return reason">
                            @foreach($reasons as $reason)
                                <input type="radio" class="btn-check" name="reason" id="reason-{{ $loop->index }}" value="{{ $reason }}"
                                       {{ old('reason', 'Damaged') === $reason ? 'checked' : '' }} required>
                                <label class="btn btn-sm btn-outline-secondary" for="reason-{{ $loop->index }}">{{ $reason }}</label>
                            @endforeach
                        </div>
                        <label class="form-label small" for="returnNote">Note (optional)</label>
                        <input type="text" name="note" id="returnNote" class="form-control mb-3" maxlength="255" value="{{ old('note') }}" placeholder="e.g. 3 bottles leaking">

                        <div class="d-flex justify-content-between border-top pt-3 mb-1">
                            <span class="text-muted">Items going back</span><strong id="sumQty">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Credit from supplier</span><strong class="text-success">Tsh <span id="sumAmount">0.00</span></strong>
                        </div>
                        <button class="btn btn-danger w-100" id="submitReturn" disabled>
                            <i class="bi bi-box-arrow-left"></i> Record return
                        </button>
                        <p class="small text-muted mt-2 mb-0">The items are taken out of stock and the invoice total is reduced. On a credit invoice, what you still owe the supplier is reduced too.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('pages.returns._purchase_return_scripts')
@endsection
