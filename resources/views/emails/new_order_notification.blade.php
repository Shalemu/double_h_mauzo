<!DOCTYPE html>
<html>
<head>
    <title>New Order Notification</title>
</head>
<body>
    <h3>New Order Submitted</h3>

    <p>Staff: {{ $order->staff->first_name }} {{ $order->staff->last_name }}</p>
    <p>Shop: {{ $order->shop->name ?? 'N/A' }}</p>
    <p>Total Amount: Tsh {{ number_format($order->total_amount, 2) }}</p>
    <p>Status: {{ ucfirst($order->status) }}</p>

    <h4>Items:</h4>
    <ul>
        @foreach($order->items as $item)
            <li>{{ $item->product->name ?? 'N/A' }} - Qty: {{ $item->quantity }}, Price: Tsh {{ number_format($item->price, 2) }}, Total: Tsh {{ number_format($item->total, 2) }}</li>
        @endforeach
    </ul>

    <p>Please review and approve the order in the admin dashboard.</p>
</body>
</html>