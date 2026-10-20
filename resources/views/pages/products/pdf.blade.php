<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products List</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #222; }
        h3 { margin: 0 0 2px; color: #831619; }
        .meta { color: #666; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 4px; }
        th { background-color: #f3e8e8; text-align: left; }
        td.num { text-align: right; }
        tr:nth-child(even) td { background: #fafafa; }
    </style>
</head>
<body>
    <h3>Double H Cosmetics — Products List</h3>
    <div class="meta">
        {{ $shopName ?? 'All shops' }}
        @if(($filter ?? 'all') !== 'all') · {{ ucwords(str_replace('_', ' ', $filter)) }} @endif
        · {{ number_format($products->count()) }} products · {{ now()->format('d M Y H:i') }}
    </div>
    {{-- One table per 100 rows: DomPDF lays out each table in memory at once,
         so a single table of thousands of rows exhausted even 512MB. --}}
    @foreach($products->chunk(100) as $chunk)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                @if(($shopName ?? 'All shops') === 'All shops')<th>Shop</th>@endif
                <th>Brand</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Qty</th>
                <th>Min Qty</th>
                <th>Purchase</th>
                <th>Selling</th>
                <th>Wholesale</th>
                <th>Barcode</th>
                <th>Expire Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chunk as $index => $product)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $product->name }}</td>
                @if(($shopName ?? 'All shops') === 'All shops')<td>{{ $product->shop_name ?? '-' }}</td>@endif
                <td>{{ $product->brand ?: '-' }}</td>
                <td>{{ $product->category_name ?? '-' }}</td>
                <td>{{ $product->unit_name ?? '-' }}</td>
                <td class="num">{{ number_format((int) $product->quantity) }}</td>
                <td class="num">{{ number_format((int) $product->min_quantity) }}</td>
                <td class="num">{{ number_format((float) $product->purchase_price, 2) }}</td>
                <td class="num">{{ number_format((float) $product->selling_price, 2) }}</td>
                <td class="num">{{ number_format((float) $product->wholesale_price, 2) }}</td>
                <td>{{ $product->barcode ?: '-' }}</td>
                <td>{{ $product->expire_date ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach
</body>
</html>
