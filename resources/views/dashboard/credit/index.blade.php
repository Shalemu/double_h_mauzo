

@php
use Carbon\Carbon;
@endphp


<div class="container-fluid mt-4 main-content">
    <div class="container-fluid align-items-center" style="max-width: 1300px; margin: 0 auto;">
        <button type="button" class="btn btn-outline-secondary float-end" data-bs-toggle="modal" data-bs-target="#depositModal">
    Deposit
</button>

        <h3 class="mb-4 text-center">Credit Purchases</h3>

      
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-info h-100 text-center">
                    <div class="card-header bg-info text-white">Today</div>
                    <div class="card-body">
                        <h5>Tsh {{ number_format($dailyCredit ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-primary h-100 text-center">
                    <div class="card-header bg-primary text-white">This Month</div>
                    <div class="card-body">
                        <h5>Tsh {{ number_format($monthlyCredit ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-dark h-100 text-center">
                    <div class="card-header bg-dark text-white">Total Credit</div>
                    <div class="card-body">
                        <h5>Tsh {{ number_format($totalCredit ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== CREDIT PURCHASES TABLE =================== --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white text-center">
                <h5 class="mb-0">All Credit Purchases</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Supplier</th>
                            <th>Shop</th>
                            <th>Total Amount</th>
                            <th>Remaining Credit</th>
                            <th>Purchase Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($creditByDate as $date => $data)
                        {{-- Date Header --}}
                        <tr class="table-secondary">
                            <td colspan="8" class="text-start">
                                <strong>{{ $date }}</strong>
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
                                <td class="text-danger">{{ number_format($invoice->remaining_amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('Y-m-d') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-history" 
                                            data-url="{{ route('purchases.history', $invoice->id) }}">
                                        History
                                    </button>

                                    <a href="{{ route('purchases.print', $invoice->id) }}" 
                                    target="_blank" class="btn btn-sm btn-secondary">
                                        Print
                                    </a>
                                </td>
                            </tr>
                        @endforeach
             

                    @empty
                        <tr>
                            <td colspan="7">No credit purchases found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('purchases.deposit') }}">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Deposit Credit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Supplier -->
                    <div class="mb-3">
                        <label>Supplier</label>
                        <select id="supplierSelect" class="form-control">
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
                        <label>Select Invoice</label>
                        <select name="purchase_invoice_id" id="purchaseSelect" class="form-control" required>
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
                        <label>Deposit Amount</label>
                        <input type="number" name="amount" class="form-control" required min="1">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Save Deposit</button>
                </div>
            </div>
        </form>
    </div>
</div>

    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Payment History</h5>
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
