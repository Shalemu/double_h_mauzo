<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales</title>
    @include('pages.products._pdf_styles')
</head>
<body>
    <h3>Double H Cosmetics — Sales</h3>
    <div class="meta">
        {{ $shopName }} · {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
        · {{ number_format($rows->count()) }} sale(s) · printed {{ now()->format('d M Y H:i') }}
    </div>
    {{-- One table per 100 rows keeps DomPDF's memory use down. --}}
    @foreach($rows->chunk(100) as $chunk)
    <table>
        <thead>
            <tr>
                <th>Sale #</th>
                <th>Date</th>
                @if($shopName === 'All shops')<th>Shop</th>@endif
                <th>Sold by</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Payment</th>
                <th>Total (Tsh)</th>
                <th>Paid (Tsh)</th>
                <th>Balance (Tsh)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chunk as $row)
                <tr>
                    <td>{{ $row['id'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    @if($shopName === 'All shops')<td>{{ $row['shop'] }}</td>@endif
                    <td>{{ $row['sold_by'] }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td class="num">{{ $row['items'] }}</td>
                    <td>{{ $row['payment'] }}</td>
                    <td class="num">{{ number_format($row['total'], 2) }}</td>
                    <td class="num">{{ number_format($row['paid'], 2) }}</td>
                    <td class="num">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    <table style="margin-top:8px">
        <tr>
            <th>Total</th>
            <th>{{ number_format($rows->count()) }} sale(s)</th>
            <th>Items: {{ number_format($rows->sum('items')) }}</th>
            <th>Total: Tsh {{ number_format($rows->sum('total'), 2) }}</th>
            <th>Paid: Tsh {{ number_format($rows->sum('paid'), 2) }}</th>
            <th>Balance: Tsh {{ number_format($rows->sum('balance'), 2) }}</th>
        </tr>
    </table>
</body>
</html>
