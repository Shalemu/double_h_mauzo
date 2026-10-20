<h5>Invoice: {{ $invoice->invoice_number ?? 'No Invoice' }}</h5>
<p>Supplier: {{ $invoice->supplier->name ?? '-' }}</p>
<p>Total Amount: Tsh {{ number_format($invoice->total_amount, 2) }}</p>
<p>Remaining Credit: Tsh {{ number_format($invoice->remaining_amount, 2) }}</p>

<hr>

<h6>Payment History</h6>
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Amount Paid</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->payments as $payment)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ number_format($payment->amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No payments found</td>
            </tr>
        @endforelse
    </tbody>
</table>