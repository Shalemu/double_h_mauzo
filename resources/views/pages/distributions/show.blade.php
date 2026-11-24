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

    {{-- What the distributor sold on the POS during the trip (recorded as they sold). --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Sales on this trip <span class="badge bg-secondary">{{ $posSales->count() }}</span></h6>
            <div class="small text-muted">
                Sold <strong class="text-body">TZS {{ number_format($posTotal, 2) }}</strong>
                · cash collected <strong class="text-success">TZS {{ number_format($posCash, 2) }}</strong>
                · on credit <strong class="text-danger">TZS {{ number_format($posCredit, 2) }}</strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center"><tr><th>Sale</th><th>Time</th><th class="text-start">Customer</th><th>Units</th><th>Payment</th><th class="text-end">Total</th><th class="text-end">Cash collected</th><th class="text-end">Credit</th></tr></thead>
                <tbody>
                    @forelse($posSales as $sale)
                        <tr>
                            <td class="text-center"><a href="{{ route('admin.sales.show', $sale->id) }}">#{{ $sale->id }}</a></td>
                            <td class="text-center text-nowrap">{{ $sale->created_at->format('d M H:i') }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td class="text-center">{{ $sale->items->sum('quantity') }}</td>
                            <td class="text-center">{{ $sale->payment_type ?: ucfirst($sale->payment_method) }}</td>
                            <td class="text-end">{{ number_format($sale->total, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->received_amount - $sale->change_amount, 2) }}</td>
                            <td class="text-end {{ $sale->remaining_amount > 0 ? 'text-danger' : '' }}">{{ number_format($sale->remaining_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted small py-3">No sales recorded on the POS{{ $distribution->isOut() ? ' yet' : '' }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($distribution->isOut())
        {{-- Distributor is back: count what they returned and the cash they handed in. --}}
        <form method="POST" action="{{ route('distributions.settle', $distribution) }}" class="card shadow-sm" id="settleForm">
            @csrf
            <div class="card-header bg-white">
                <h6 class="mb-0">Settle — distributor is back</h6>
                <small class="text-muted">
                    Sales recorded on the POS are already counted. Enter how many of each product came back: returned units go back into the store;
                    anything neither sold on the POS nor returned is recorded as a sale now.
                </small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr><th>Product</th><th>Taken</th><th>Sold on POS</th><th style="width:130px">Returned</th><th>Not recorded</th><th style="width:160px">Price (TZS)</th><th>Amount (TZS)</th></tr>
                        </thead>
                        <tbody>
                            @foreach($distribution->items as $item)
                                @php $carried = $item->quantity_taken - $item->quantity_recorded; @endphp
                                <tr data-taken="{{ $carried }}" data-taken-total="{{ $item->quantity_taken }}" data-recorded="{{ $item->quantity_recorded }}">
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity_taken }}</td>
                                    <td class="text-center text-primary fw-semibold">{{ $item->quantity_recorded }}</td>
                                    <td>
                                        <input type="number" name="items[{{ $item->id }}][returned]" class="form-control form-control-sm text-center js-returned"
                                               min="0" max="{{ $carried }}" value="{{ old("items.{$item->id}.returned", 0) }}" required
                                               aria-label="Returned {{ $item->product_name }}">
                                    </td>
                                    <td class="text-center js-sold">{{ $carried }}</td>
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
                            <tr class="table-light"><th colspan="6" class="text-end">Not recorded, sold now</th><th class="text-end" id="settleTotal">0.00</th></tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Cash the distributor should hand in: POS cash they collected + the not-recorded sales. --}}
                <div class="row g-3 align-items-end" id="settleCash" data-pos-cash="{{ $posCash }}">
                    <div class="col-md-3">
                        <div class="text-muted small">Cash expected</div>
                        <div class="fs-5 fw-semibold">TZS <span id="settleExpected">0.00</span></div>
                        <div class="small text-muted">POS cash {{ number_format($posCash, 2) }} + not recorded</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="amountReceived">Cash handed in (TZS)</label>
                        <input type="number" name="amount_received" id="amountReceived" class="form-control" min="0" step="0.01"
                               value="{{ old('amount_received', 0) }}" required>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Still owed by distributor</div>
                        <div class="fs-5 fw-semibold" id="settleBalance">0.00</div>
                    </div>
                    <div class="col-md-3 text-md-end">
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
                                        <div class="small text-muted"><i class="bi bi-receipt text-primary"></i> Not recorded → sold now</div>
                                        <div class="fs-4 fw-semibold" id="cfSold">0</div>
                                        <div class="small text-muted">units · TZS <span id="cfTotal">0</span></div>
                                        <div class="small text-muted">+ {{ $posSales->count() }} POS sale(s), TZS {{ number_format($posTotal, 2) }}</div>
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
                                        <tr><th class="text-start">Product</th><th>Taken</th><th>Sold on POS</th><th>Returned</th><th>Not recorded</th><th>Amount (TZS)</th></tr>
                                    </thead>
                                    <tbody id="cfLines"></tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning small mt-3 mb-0 d-none" id="cfOverpaid">
                                <i class="bi bi-exclamation-triangle"></i> Cash handed in is more than the cash expected — only the expected amount will be recorded.
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

        @include('pages.distributions._show_scripts')
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0">What happened to the stock</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr><th>Product</th><th>Taken</th><th>Sold on POS</th><th>Returned to store</th><th>Not recorded (sold at settle)</th><th>Price (TZS)</th><th>Amount at settle (TZS)</th></tr>
                        </thead>
                        <tbody>
                            @foreach($distribution->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity_taken }}</td>
                                    <td class="text-center text-primary">{{ $item->quantity_recorded }}</td>
                                    <td class="text-center">{{ $item->quantity_returned }}</td>
                                    <td class="text-center">{{ $item->quantity_unrecorded }}</td>
                                    <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->quantity_unrecorded * $item->price, 2) }}</td>
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
