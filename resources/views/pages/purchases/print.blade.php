<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 28px; }
        .header h3 { margin: 5px 0; font-weight: normal; color: #555; }
        .details { margin-bottom: 20px; }
        .details p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        table th { background-color: #f5f5f5; }
        .totals { width: 300px; float: right; margin-top: 20px; }
        .totals table { border: none; }
        .totals th, .totals td { border: none; text-align: right; padding: 5px 10px; }
        .totals th { font-weight: bold; }
        .totals td { font-size: 16px; }
        .highlight { font-size: 18px; font-weight: bold; color: #d9534f; }
    </style>
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
                <td>{{ $item->quantity }}</td>
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