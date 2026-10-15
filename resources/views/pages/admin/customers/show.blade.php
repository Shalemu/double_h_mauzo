@extends('main')

@section('title', 'Customer Details')

@section('content')

<style>
.customer-detail-page .card {
    border:1px solid #e8edf4;
    border-radius:8px;
    overflow:hidden;
}
.customer-detail-page .customer-profile-header {
    background:#fff3cd;
    color:#664d03;
    padding:18px 22px;
}
.customer-detail-page .customer-profile-body { padding:22px; }
.customer-detail-page .customer-contact-item {
    height:100%;
    padding:12px 14px;
    background:#f7f9fc;
    border:1px solid #edf0f5;
    border-radius:8px;
}
.customer-detail-page .summary-card {
    height:100%;
    padding:14px 16px;
    background:linear-gradient(135deg,#fff 0%,#f8faff 100%);
    transition:box-shadow .2s ease, transform .2s ease;
    border-radius:8px;
}
.customer-detail-page .summary-card:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(31,41,55,.07);
}
.customer-detail-page .history-header { background:#f8f9fc; }
.customer-detail-page .table thead th {
    color:#647084;
    font-size:.75rem;
    letter-spacing:.04em;
    text-transform:uppercase;
    white-space:nowrap;
}
.customer-detail-page .customer-stat-icon {
    width:42px;
    height:42px;
    flex:0 0 42px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    font-size:18px;
}
.customer-detail-page .customer-stat-label {
    color:#6b7280;
    font-size:12px;
    font-weight:500;
    margin-bottom:3px;
}
.customer-detail-page .customer-stat-value {
    font-size:21px;
    font-weight:700;
    line-height:1.15;
    margin:0;
}
.customer-detail-page .history-table-wrap { border-radius:8px; overflow:hidden; }
</style>

<div class="container-fluid py-4 customer-detail-page">
        <div class="row g-4">
            <div class="col-12">

                <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm mb-3">
                    <i class="fa fa-arrow-left"></i> Back to Customers
                </a>

                {{-- Customer Profile Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header customer-profile-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $customer->name }}</h5>
                        @if($totalDebt > 0)
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                                <i class="fa fa-credit-card"></i> Reduce Debt
                            </button>
                        @endif
                    </div>
                    <div class="card-body customer-profile-body">
                        <div class="row g-3">
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Phone</div><strong>{{ $customer->phone ?? '-' }}</strong></div></div>
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Email</div><strong>{{ $customer->email ?? '-' }}</strong></div></div>
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Address</div><strong>{{ $customer->address ?? '-' }}</strong></div></div>
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Shop</div><strong>{{ $customer->shop->name ?? '-' }}</strong></div></div>
                        </div>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card summary-card shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="customer-stat-icon" style="background:#e9f9ef;color:#16a34a;"><i class="bi bi-bag-check"></i></div>
                                <div><div class="customer-stat-label">Total Purchases</div><h4 class="customer-stat-value text-success">{{ number_format($totalPurchases ?? 0) }}</h4></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card summary-card shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="customer-stat-icon" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-wallet2"></i></div>
                                <div><div class="customer-stat-label">Total Paid</div><h4 class="customer-stat-value text-primary" id="totalPaid">{{ number_format($totalPaid ?? 0) }}</h4></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card summary-card shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="customer-stat-icon" style="background:#fdeceb;color:#ef4444;"><i class="bi bi-exclamation-circle"></i></div>
                                <div><div class="customer-stat-label">Total Debt</div><h4 class="customer-stat-value text-danger" id="totalDebt">{{ number_format($totalDebt ?? 0) }}</h4></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Purchase History --}}
                <div class="card shadow-sm mb-5">
                    <div class="card-header history-header">
                        <h6 class="mb-0">Purchase History</h6>
                    </div>

                    <div class="card-body p-0">
                      <div class="table-responsive history-table-wrap">
                        <table class="table table-bordered table-hover text-center">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Debt</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $index => $sale)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}</td>
                                        <td>{{ number_format($sale->total) }}</td>
                                        <td>{{ number_format($sale->received_amount) }}</td>
                                        <td>{{ number_format($sale->remaining_amount) }}</td>
                                        <td>
                                            @if($sale->remaining_amount > 0)
                                                <span class="badge bg-danger">Debt</span>
                                            @else
                                                <span class="badge bg-success">Paid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No purchases found for this customer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                      </div>
                    </div>
                </div>

            </div>
        </div>
</div>

{{-- Modal: Record Payment --}}
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentLabel" aria-hidden="true" style="margin-top: 150px;">
  <div class="modal-dialog">
    <form id="recordPaymentForm">
        @csrf
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentLabel">Record Payment for {{ $customer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>Current Debt: <strong id="currentDebt">{{ number_format($totalDebt ?? 0) }}</strong></p>
                <div class="mb-3">
                    <label for="amountPaid" class="form-label">Amount Paid</label>
                    <input type="number" class="form-control" name="amount_paid" id="amountPaid" min="0" max="{{ $totalDebt ?? 0 }}" required>
                </div>
                <p>Remaining Debt after payment: <strong id="remainingDebt">{{ number_format($totalDebt ?? 0) }}</strong></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">Submit Payment</button>
            </div>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amountPaid');
    const remainingDebtEl = document.getElementById('remainingDebt');
    const totalDebt = Number("{{ $totalDebt ?? 0 }}");

    function updateRemaining() {
        let paid = parseFloat(amountInput.value) || 0;
        let remaining = Math.max(totalDebt - paid, 0);
        remainingDebtEl.textContent = remaining.toLocaleString();
    }

    amountInput.addEventListener('input', updateRemaining);

    const form = document.getElementById('recordPaymentForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        let formData = new FormData(form);

        try {
            const response = await fetch("{{ route('customers.recordPayment', $customer) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': formData.get('_token') },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                document.getElementById('totalDebt').textContent = Number(data.remaining_debt).toLocaleString();
                document.getElementById('totalPaid').textContent = Number(data.total_paid).toLocaleString();
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('recordPaymentModal')).hide();
                location.reload();
            } else {
                alert(data.message);
            }
        } catch(err) {
            alert("Error processing payment: " + err.message);
        }
    });
});
</script>

@endsection
