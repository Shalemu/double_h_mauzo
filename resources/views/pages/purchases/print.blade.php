<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    @include('pages.purchases._print_styles')
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h2>{{ $invoice->shop->name ?? 'Shop' }}</h2>
        <h3>Invoice #{{ $invoice->invoice_number ?? '-' }}</h3>
    </div>

    <!-- Supplier & Date -->
    <div class="details">
        <p><strong>Supplier:</strong> {{ $invoice->supplier->name ?? '-' }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->purchased_at)->format('Y-m-d') }}</p>
    </div>

    <!-- Products Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td>{{ $invoice->isPending() ? $item->ordered_quantity : $item->quantity }}</td>
                <td>{{ number_format($item->purchase_price,2) }}</td>
                <td>{{ number_format($item->total,2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Section -->
    <div class="totals">
        <table>
            <tr>
                <th>Total Amount:</th>
                <td>{{ number_format($invoice->total_amount,2) }} Tsh</td>
            </tr>
            <tr>
                <th>Paid:</th>
                <td>{{ number_format($invoice->amount_paid,2) }} Tsh</td>
            </tr>
            <tr class="highlight">
                <th>Remaining:</th>
                <td>{{ number_format($invoice->remaining_amount,2) }} Tsh</td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>

    <script>
        window.print(); // Auto print when page loads
    </script>
</div>
</body>
</html>