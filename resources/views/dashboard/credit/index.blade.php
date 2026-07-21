

@php
use Carbon\Carbon;
@endphp


<div class="container-fluid mt-4">
    <div class="container-fluid align-items-center px-0">

        <style>
            .credit-stat-card {
                border: none;
                border-radius: 1rem;
                overflow: hidden;
                transition: transform .18s ease, box-shadow .18s ease;
                box-shadow: 0 .25rem .75rem rgba(0,0,0,.06);
            }
            .credit-stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.1);
            }
            .credit-stat-icon {
                width: 52px;
                height: 52px;
                border-radius: 14px;
                flex: 0 0 52px;
            }
            .credit-stat-label {
                font-size: .72rem;
                letter-spacing: .04em;
            }
            .credit-stat-value {
                font-size: 1.3rem;
            }
            .credit-table-card {
                border: none;
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 .25rem .75rem rgba(0,0,0,.06);
            }
            .credit-table-card thead th {
                font-size: .75rem;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #6c757d;
                border-bottom-width: 1px;
            }
            .credit-table-card tbody tr:hover {
                background-color: #f8f9fb;
            }
            .credit-date-row td {
                background: #eef1f6;
                font-weight: 600;
                color: #495057;
            }
            .remaining-credit-badge {
                font-weight: 600;
            }
        </style>

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h3 class="mb-0"><i class="bi bi-credit-card-2-back me-2 text-primary"></i>Credit Purchases</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="bi bi-cash-coin me-1"></i> Deposit
            </button>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6 d-flex">
                <div class="card credit-stat-card w-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="credit-stat-icon bg-info text-white d-flex align-items-center justify-content-center me-3">
                            <i class="bi bi-calendar-day fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase credit-stat-label fw-semibold mb-1">Today</div>
                            <div class="fw-bold credit-stat-value text-info">Tsh {{ number_format($dailyCredit ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 d-flex">
                <div class="card credit-stat-card w-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="credit-stat-icon bg-primary text-white d-flex align-items-center justify-content-center me-3">
                            <i class="bi bi-calendar3 fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase credit-stat-label fw-semibold mb-1">This Month</div>
                            <div class="fw-bold credit-stat-value text-primary">Tsh {{ number_format($monthlyCredit ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 d-flex">
                <div class="card credit-stat-card w-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="credit-stat-icon bg-dark text-white d-flex align-items-center justify-content-center me-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase credit-stat-label fw-semibold mb-1">Total Credit</div>
                            <div class="fw-bold credit-stat-value">Tsh {{ number_format($totalCredit ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== CREDIT PURCHASES TABLE =================== --}}
        <div class="card credit-table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Credit Purchases</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Supplier</th>
                            <th>Shop</th>
                            <th>Total Amount</th>
                            <th>Remaining Credit</th>
                            <th>Purchase Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($creditByDate as $date => $data)
                        {{-- Date Header --}}
                        <tr class="credit-date-row">
                            <td colspan="8">
                                <i class="bi bi-calendar-event me-2"></i>{{ $date }}
                            </td>
                        </tr>

                         {{-- Credit Rows --}}
                        @foreach($data['items'] as $invoice)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $invoice->invoice_number ?? '-' }}</td>
                                <td>{{ $invoice->supplier->name ?? '-' }}</td>
                                <td>{{ $invoice->shop->name ?? '-' }}</td>
                                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger remaining-credit-badge">
                                        {{ number_format($invoice->remaining_amount, 2) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info view-history"
                                            data-url="{{ route('purchases.history', $invoice->id) }}">
                                        <i class="bi bi-clock-history"></i> History
                                    </button>

                                    <a href="{{ route('purchases.print', $invoice->id) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-printer"></i> Print
                                    </a>
                                </td>
                            </tr>
                        @endforeach


                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No credit purchases found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('purchases.deposit') }}">
            @csrf

            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin me-2 text-primary"></i>Deposit Credit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Supplier -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier</label>
                        <select id="supplierSelect" class="form-select">
                            <option value="">-- All Suppliers --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Purchase -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Invoice</label>
                        <select name="purchase_invoice_id" id="purchaseSelect" class="form-select" required>
                        <option value="">-- Select Invoice --</option>
                        @foreach($creditByDate as $date => $data)
                            @foreach($data['items'] as $invoice)
                                <option value="{{ $invoice->id }}" data-supplier="{{ $invoice->supplier_id }}">
                                    {{ $invoice->invoice_number ?? 'No Invoice' }}
                                    - Remaining: {{ number_format($invoice->remaining_amount,2) }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deposit Amount</label>
                        <input type="number" name="amount" class="form-control" required min="1">
                    </div>

                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save Deposit</button>
                </div>
            </div>
        </form>
    </div>
</div>

    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Invoice Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('supplierSelect').addEventListener('change', function () {
    let supplierId = this.value;
    let options = document.querySelectorAll('#purchaseSelect option');

    options.forEach(option => {
        if (!option.value) return; // skip default

        if (!supplierId || option.dataset.supplier === supplierId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });

    // reset selected value
    document.getElementById('purchaseSelect').value = '';
});

// credit history
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-history').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(this.dataset.url)
                .then(res => res.text())
                .then(html => {
                    const modal = document.getElementById('historyModal');
                    modal.querySelector('.modal-body').innerHTML = html;
                    new bootstrap.Modal(modal).show();
                });
        });
    });
});

</script>
