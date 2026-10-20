@extends('main')

@section('title', 'Distribution #' . $distribution->id)

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="mb-3">
        <a href="{{ route('distributions.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> All distributions</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-truck-flatbed me-2"></i>Distribution #{{ $distribution->id }}</h5>
            @if($distribution->isOut())
                <span class="badge bg-warning text-dark">Out with distributor</span>
            @else
                <span class="badge bg-success">Settled {{ $distribution->settled_at?->format('d M Y H:i') }}</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Store</div><div class="fw-semibold">{{ $distribution->store->name ?? '—' }}</div></div>
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Distributor</div><div class="fw-semibold">{{ $distribution->staff->full_name ?? '—' }}</div></div>
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Issued</div><div class="fw-semibold">{{ $distribution->created_at->format('d M Y H:i') }} by {{ $distribution->issuedBy->name ?? '—' }}</div></div>
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Note</div><div class="fw-semibold">{{ $distribution->note ?: '—' }}</div></div>
            </div>
        </div>
    </div>

    @if($distribution->isOut())
        {{-- Distributor is back: count what they returned and the cash they handed in. --}}
        <form method="POST" action="{{ route('distributions.settle', $distribution) }}" class="card shadow-sm" id="settleForm">
            @csrf
            <div class="card-header bg-white">
                <h6 class="mb-0">Settle — distributor is back</h6>
                <small class="text-muted">Enter how many of each product came back. Returned units go back into the store; the rest is recorded as a sale.</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr><th>Product</th><th>Taken</th><th style="width:130px">Returned</th><th>Sold</th><th style="width:160px">Price (TZS)</th><th>Amount (TZS)</th></tr>
                        </thead>
                        <tbody>
                            @foreach($distribution->items as $item)
                                <tr data-taken="{{ $item->quantity_taken }}">
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity_taken }}</td>
                                    <td>
                                        <input type="number" name="items[{{ $item->id }}][returned]" class="form-control form-control-sm text-center js-returned"
                                               min="0" max="{{ $item->quantity_taken }}" value="{{ old("items.{$item->id}.returned", 0) }}" required
                                               aria-label="Returned {{ $item->product_name }}">
                                    </td>
                                    <td class="text-center js-sold">{{ $item->quantity_taken }}</td>
                                    <td>
                                        <input type="number" name="items[{{ $item->id }}][price]" class="form-control form-control-sm text-end js-price"
                                               min="0" step="0.01" value="{{ old("items.{$item->id}.price", $item->price) }}" required
                                               aria-label="Price {{ $item->product_name }}">
                                    </td>
                                    <td class="text-end js-amount">0.00</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light"><th colspan="5" class="text-end">Total sold</th><th class="text-end" id="settleTotal">0.00</th></tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="amountReceived">Cash handed in (TZS)</label>
                        <input type="number" name="amount_received" id="amountReceived" class="form-control" min="0" step="0.01"
                               value="{{ old('amount_received', 0) }}" required>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Still owed by distributor</div>
                        <div class="fs-5 fw-semibold" id="settleBalance">0.00</div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button type="button" class="btn btn-primary" id="settleReview">
                            <i class="bi bi-check2-circle"></i> Settle distribution
                        </button>
                    </div>
                </div>
            </div>

            {{-- Review before settling: shows exactly what will happen to stock and money. --}}
            <div class="modal fade" id="settleConfirmModal" tabindex="-1" aria-labelledby="settleConfirmTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header">
                            <h5 class="modal-title" id="settleConfirmTitle"><i class="bi bi-clipboard-check me-2"></i>Confirm settlement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                <strong>{{ $distribution->staff->full_name ?? 'The distributor' }}</strong> is back.
                                Check the figures below — once settled, this can't be changed.
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted"><i class="bi bi-box-arrow-in-down text-success"></i> Back to {{ $distribution->store->name ?? 'store' }}</div>
                                        <div class="fs-4 fw-semibold" id="cfReturned">0</div>
                                        <div class="small text-muted">units</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted"><i class="bi bi-receipt text-primary"></i> Recorded as sold</div>
                                        <div class="fs-4 fw-semibold" id="cfSold">0</div>
                                        <div class="small text-muted">units · TZS <span id="cfTotal">0</span></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted"><i class="bi bi-cash-stack text-success"></i> Cash handed in</div>
                                        <div class="fs-4 fw-semibold">TZS <span id="cfReceived">0</span></div>
                                        <div class="small" id="cfBalanceLine"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light text-center">
                                        <tr><th class="text-start">Product</th><th>Taken</th><th>Returned</th><th>Sold</th><th>Amount (TZS)</th></tr>
                                    </thead>
                                    <tbody id="cfLines"></tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning small mt-3 mb-0 d-none" id="cfOverpaid">
                                <i class="bi bi-exclamation-triangle"></i> Cash handed in is more than the sold amount — only the sold amount will be recorded.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Go back and edit</button>
                            <button type="submit" class="btn btn-primary" id="settleSubmit"><i class="bi bi-check2-circle"></i> Confirm &amp; settle</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('settleForm');
            const modalEl = document.getElementById('settleConfirmModal');
            const modal = new bootstrap.Modal(modalEl);
            const fmt = n => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('settleReview').addEventListener('click', function () {
                // Let the browser flag empty/invalid fields first.
                if (!form.reportValidity()) return;

                let returned = 0, sold = 0, total = 0;
                const lines = document.getElementById('cfLines');
                lines.replaceChildren();

                form.querySelectorAll('tbody tr[data-taken]').forEach(function (row) {
                    const taken = parseInt(row.dataset.taken, 10);
                    const back = Math.min(Math.max(parseInt(row.querySelector('.js-returned').value, 10) || 0, 0), taken);
                    const price = parseFloat(row.querySelector('.js-price').value) || 0;
                    const out = taken - back;
                    returned += back; sold += out; total += out * price;

                    const tr = document.createElement('tr');
                    [[row.cells[0].textContent.trim(), ''], [taken, 'text-center'], [back, 'text-center text-success'],
                     [out, 'text-center fw-semibold'], [fmt(out * price), 'text-end']].forEach(function ([text, cls]) {
                        const td = document.createElement('td');
                        td.textContent = text;
                        td.className = cls;
                        tr.appendChild(td);
                    });
                    lines.appendChild(tr);
                });

                const received = parseFloat(document.getElementById('amountReceived').value) || 0;
                const balance = Math.max(total - received, 0);
                document.getElementById('cfReturned').textContent = returned;
                document.getElementById('cfSold').textContent = sold;
                document.getElementById('cfTotal').textContent = fmt(total);
                document.getElementById('cfReceived').textContent = fmt(Math.min(received, total));

                const balanceLine = document.getElementById('cfBalanceLine');
                balanceLine.className = 'small fw-semibold ' + (balance > 0 ? 'text-danger' : 'text-success');
                balanceLine.textContent = balance > 0
                    ? 'Still owes TZS ' + fmt(balance)
                    : 'Fully paid';
                document.getElementById('cfOverpaid').classList.toggle('d-none', received <= total);

                modal.show();
            });

            // Enter in a field opens the review instead of settling straight away.
            form.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && e.target.tagName === 'INPUT' && !modalEl.contains(e.target)) {
                    e.preventDefault();
                    document.getElementById('settleReview').click();
                }
            });

            // Stop double submits while the settlement saves.
            form.addEventListener('submit', function () {
                const btn = document.getElementById('settleSubmit');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Settling...';
            });
        });
        </script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('settleForm');
            const received = document.getElementById('amountReceived');
            const fmt = n => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            function recalc() {
                let total = 0;
                form.querySelectorAll('tbody tr[data-taken]').forEach(function (row) {
                    const taken = parseInt(row.dataset.taken, 10);
                    const returned = Math.min(Math.max(parseInt(row.querySelector('.js-returned').value, 10) || 0, 0), taken);
                    const price = parseFloat(row.querySelector('.js-price').value) || 0;
                    const sold = taken - returned;
                    row.querySelector('.js-sold').textContent = sold;
                    row.querySelector('.js-amount').textContent = fmt(sold * price);
                    total += sold * price;
                });
                document.getElementById('settleTotal').textContent = fmt(total);
                const balance = Math.max(total - (parseFloat(received.value) || 0), 0);
                const el = document.getElementById('settleBalance');
                el.textContent = fmt(balance);
                el.classList.toggle('text-danger', balance > 0);
            }

            form.addEventListener('input', recalc);
            recalc();
        });
        </script>
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0">What happened to the stock</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr><th>Product</th><th>Taken</th><th>Returned to store</th><th>Sold</th><th>Price (TZS)</th><th>Amount (TZS)</th></tr>
                        </thead>
                        <tbody>
                            @foreach($distribution->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity_taken }}</td>
                                    <td class="text-center">{{ $item->quantity_returned }}</td>
                                    <td class="text-center">{{ $item->quantity_sold }}</td>
                                    <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->quantity_sold * $item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-sm-4"><div class="border rounded-3 p-3"><div class="small text-muted">Total sold (TZS)</div><div class="fs-5 fw-semibold">{{ number_format($distribution->total_value, 2) }}</div></div></div>
                    <div class="col-sm-4"><div class="border rounded-3 p-3"><div class="small text-muted">Cash received (TZS)</div><div class="fs-5 fw-semibold">{{ number_format($distribution->amount_received, 2) }}</div></div></div>
                    <div class="col-sm-4"><div class="border rounded-3 p-3"><div class="small text-muted">Still owed (TZS)</div><div class="fs-5 fw-semibold {{ $distribution->balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($distribution->balance, 2) }}</div></div></div>
                </div>

                @if($distribution->sale_id)
                    <div class="small text-muted mt-3">
                        Recorded as <a href="{{ route('admin.sales.show', $distribution->sale_id) }}">sale #{{ $distribution->sale_id }}</a> from {{ $distribution->store->name ?? 'the store' }}, settled by {{ $distribution->settledBy->name ?? '—' }}.
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Payments from distributor</h6></div>
            <div class="card-body">
                @if($distribution->balance > 0)
                    <form method="POST" action="{{ route('distributions.pay', $distribution) }}" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-3">
                            <input type="number" name="amount" class="form-control" min="0.01" step="0.01" max="{{ $distribution->balance }}"
                                   placeholder="Amount (TZS)" aria-label="Amount" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="note" class="form-control" maxlength="255" placeholder="Note (optional)" aria-label="Note">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success w-100"><i class="bi bi-cash"></i> Record payment</button>
                        </div>
                    </form>
                @endif

                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light"><tr><th>Date</th><th class="text-end">Amount (TZS)</th><th>Received by</th><th>Note</th></tr></thead>
                    <tbody>
                        @forelse($distribution->payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                                <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->receivedBy->name ?? '—' }}</td>
                                <td>{{ $payment->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center small">No later payments. Cash handed in at settlement is already included above.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
