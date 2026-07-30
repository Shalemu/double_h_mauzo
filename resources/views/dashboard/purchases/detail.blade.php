<h6>Purchases on {{ $date }}</h6>
<table class="table table-sm table-bordered text-center">
    <thead>
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Supplier</th>
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