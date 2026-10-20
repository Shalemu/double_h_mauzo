<h6>Invoices on {{ $date }}</h6>
<div class="table-responsive mb-4">
<table class="table table-sm table-bordered text-center align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th>Invoice</th>
            <th>Time</th>
            <th>Supplier</th>
            <th>Units</th>
            <th>Total (Tsh)</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices ?? [] as $invoice)
            @php $owed = (float) $invoice->remaining_amount; @endphp
            <tr>
                <td class="fw-semibold">{{ \App\Helpers\InvoiceNumber::for($invoice) }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('H:i') }}</td>
                <td>{{ $invoice->supplier->name ?? '-' }}</td>
                <td>{{ number_format((int) $invoice->units) }}</td>
                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                <td>
                    @if(($invoice->payment_type ?? null) === 'credit' && $owed > 0)
                        <span class="badge bg-warning text-dark">Owes {{ number_format($owed) }}</span>
                    @else
                        <span class="badge bg-success">Paid</span>
                    @endif
                </td>
                <td class="text-nowrap">
                    <a href="{{ route('purchases.history', $invoice->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> View</a>
                    <a href="{{ route('purchases.invoice_pdf', $invoice->id) }}" class="btn btn-sm btn-outline-danger" aria-label="Download PDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-muted">No invoices on this day.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

<h6>Items purchased on {{ $date }}</h6>
<table class="table table-sm table-bordered text-center">
    <thead>
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Supplier</th>
            <th>Invoice</th>
            <th>Qty</th>
            <th>Retail Price</th>
            <th>Wholesale Price</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchases as $index => $p)
        <tr>
            {{-- SN --}}
            <td>{{ $index + 1 }}</td>

            {{-- Product name (check if product exists) --}}
            <td>{{ $p->product?->name ?? '-' }}</td>

            {{-- Supplier name (check if invoice and supplier exist) --}}
            <td>{{ $p->invoice?->supplier?->name ?? '-' }}</td>

            {{-- Invoice it belongs to --}}
            <td>
                @if($p->invoice)
                    <a href="{{ route('purchases.history', $p->invoice->id) }}">{{ \App\Helpers\InvoiceNumber::for($p->invoice) }}</a>
                @else
                    -
                @endif
            </td>

            {{-- Quantity --}}
            <td>{{ $p->quantity ?? 0 }}</td>

            {{-- Current retail selling price --}}
            <td>{{ $p->product?->selling_price ? number_format($p->product->selling_price, 2) : '-' }}</td>

            {{-- Current wholesale selling price --}}
            <td>{{ $p->product?->wholesale_price ? number_format($p->product->wholesale_price, 2) : '-' }}</td>

            {{-- Price --}}
            <td>{{ number_format($p->purchase_price ?? 0, 2) }}</td>

            {{-- Total --}}
            <td>{{ number_format(($p->quantity ?? 0) * ($p->purchase_price ?? 0), 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>