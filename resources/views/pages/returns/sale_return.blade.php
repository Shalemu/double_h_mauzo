@extends('main')

@section('title', 'Return items — Sale #' . $sale->id)

@section('content')
@php $money = fn ($v) => number_format((float) $v, 2); @endphp
<div class="container-fluid py-4">
    <a href="{{ route('returns.sales') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to sales returns</a>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('returns.sales.store', $sale->id) }}" id="returnForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Return items from Sale #{{ $sale->id }}</h5>
                        <small class="text-muted">Enter how many of each item the customer is bringing back. Leave 0 for items not returned.</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Product</th>
                                        <th>Sold</th>
                                        <th>Already returned</th>
                                        <th>Can return</th>
                                        <th style="width:130px">Return qty</th>
                                        <th style="width:150px">Refund (Tsh)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                        @php
                                            $already = (int) ($returned[$item->id] ?? 0);
                                            $left = (int) $item->quantity;
                                            $unit = $item->price - ((float) $item->discount / max($left, 1));
                                        @endphp
                                        <tr class="return-line" data-unit="{{ round($unit, 2) }}">
                                            <td class="text-start">
                                                {{ $item->product->name ?? 'Deleted product' }}
                                                <div class="small text-muted">{{ ucfirst($item->sale_type ?? 'retail') }} · Tsh {{ $money($item->price) }} each</div>
                                            </td>
                                            <td>{{ $left + $already }}</td>
                                            <td>{{ $already ?: '—' }}</td>
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
                                                       aria-label="Refund for {{ $item->product->name ?? 'item' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted fw-normal">Sold on</dt><dd class="col-7">{{ $sale->created_at->format('d M Y, H:i') }}</dd>
                            <dt class="col-5 text-muted fw-normal">Shop</dt><dd class="col-7">{{ $sale->shop->name ?? '—' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Customer</dt><dd class="col-7">{{ $sale->customer->name ?? 'Walk-in' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Sold by</dt><dd class="col-7">{{ $sale->staff->full_name ?? ($sale->admin->name ?? '—') }}</dd>
                            <dt class="col-5 text-muted fw-normal">Sale total</dt><dd class="col-7 mb-0">Tsh {{ $money($sale->total) }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold">Why is it being returned?</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3" role="radiogroup" aria-label="Return reason">
                            @foreach($reasons as $reason)
                                <input type="radio" class="btn-check" name="reason" id="reason-{{ $loop->index }}" value="{{ $reason }}"
                                       {{ old('reason', 'Damaged') === $reason ? 'checked' : '' }} required>
                                <label class="btn btn-sm btn-outline-secondary" for="reason-{{ $loop->index }}">{{ $reason }}</label>
                            @endforeach
                        </div>
                        <label class="form-label small" for="returnNote">Note (optional)</label>
                        <input type="text" name="note" id="returnNote" class="form-control mb-3" maxlength="255" value="{{ old('note') }}" placeholder="e.g. seal was broken">

                        <div class="d-flex justify-content-between border-top pt-3 mb-1">
                            <span class="text-muted">Items returning</span><strong id="sumQty">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Refund to customer</span><strong class="text-danger">Tsh <span id="sumAmount">0.00</span></strong>
                        </div>
                        <button class="btn btn-danger w-100" id="submitReturn" disabled>
                            <i class="bi bi-arrow-return-left"></i> Record return
                        </button>
                        <p class="small text-muted mt-2 mb-0">The items go back into stock and the sale total is reduced. Any unpaid balance on this sale is reduced first.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const lines = Array.from(document.querySelectorAll('.return-line'));
    const fmt = n => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function refresh() {
        let qty = 0, amount = 0;
        lines.forEach(function (line) {
            const q = parseInt(line.querySelector('.return-qty').value, 10) || 0;
            const a = parseFloat(line.querySelector('.return-amount').value) || 0;
            qty += q; amount += a;
            line.classList.toggle('table-warning', q > 0);
        });
        document.getElementById('sumQty').textContent = qty;
        document.getElementById('sumAmount').textContent = fmt(amount);
        document.getElementById('submitReturn').disabled = qty === 0;
    }

    lines.forEach(function (line) {
        const qty = line.querySelector('.return-qty');
        const amount = line.querySelector('.return-amount');
        const unit = parseFloat(line.dataset.unit) || 0;
        let amountEdited = amount.value !== '';

        function setQty(value) {
            const max = parseInt(qty.max, 10) || 0;
            qty.value = Math.max(0, Math.min(max, value));
            // Suggest the refund from the price paid until it's typed by hand.
            if (!amountEdited) amount.value = qty.value > 0 ? (unit * qty.value).toFixed(2) : '';
            refresh();
        }

        qty.addEventListener('input', () => setQty(parseInt(qty.value, 10) || 0));
        amount.addEventListener('input', () => { amountEdited = amount.value !== ''; refresh(); });
        line.querySelectorAll('.qty-step').forEach(btn => btn.addEventListener('click', () => setQty((parseInt(qty.value, 10) || 0) + parseInt(btn.dataset.step, 10))));
    });

    const form = document.getElementById('returnForm');
    let confirmed = false;
    form.addEventListener('submit', function (e) {
        if (confirmed) return;
        e.preventDefault();

        const qty = document.getElementById('sumQty').textContent;
        const amount = document.getElementById('sumAmount').textContent;
        const reason = (form.querySelector('[name=reason]:checked') || {}).value || '';

        Swal.fire({
            icon: 'question',
            title: 'Record this return?',
            html: '<div class="text-start">'
                + '<div class="d-flex justify-content-between"><span>Items returning</span><strong>' + qty + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>Refund to customer</span><strong class="text-danger">Tsh ' + amount + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>Reason</span><strong></strong></div>'
                + '<p class="small text-muted mt-2 mb-0">The items go back into stock and the sale total is reduced.</p>'
                + '</div>',
            didOpen: (popup) => { popup.querySelector('.d-flex:nth-child(3) strong').textContent = reason; },
            showCancelButton: true,
            confirmButtonText: 'Yes, record return',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            reverseButtons: true,
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            confirmed = true;
            const button = document.getElementById('submitReturn');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
            form.submit();
        });
    });

    refresh();
});
</script>
@endsection
