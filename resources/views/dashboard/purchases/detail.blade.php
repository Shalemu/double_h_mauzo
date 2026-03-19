<h6>Purchases on {{ $date }}</h6>
<table class="table table-sm table-bordered text-center">
    <thead>
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Supplier</th>
            <th>Qty</th>
            <th>Sale Type</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchases as $index => $p)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $p->product->name }}</td>
            <td>{{ $p->supplier->name }}</td>
            <td>{{ $p->quantity }}</td>
            <td>{{ ucfirst($p->sale_type ?? '-') }}</td>
            <td>{{ number_format($p->purchase_price, 2) }}</td>
            <td>{{ number_format($p->quantity * $p->purchase_price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
