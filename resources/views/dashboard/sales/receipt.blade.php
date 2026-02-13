<!DOCTYPE html>
<html>
<head>
    <title>Receipt #{{ $sale->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .receipt { max-width: 400px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #ccc; padding: 5px; text-align: left; }
        th { text-align: left; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="receipt">
        <h3>My Shop</h3>
        <p>Receipt #{{ $sale->id }}<br>Date: {{ $sale->created_at->format('d/m/Y H:i') }}</p>
        <p>Customer: {{ $sale->customer->name ?? 'Walk-in' }}</p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ number_format($item->discount, 2) }}</td>
                    <td>{{ number_format(($item->price * $item->quantity) - $item->discount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="total">Bill Discount: {{ number_format($sale->bill_discount, 2) }}<br>
        Grand Total: {{ number_format($sale->total, 2) }}</p>

        <p>Thank you for shopping!</p>
    </div>
</body>
</html>
