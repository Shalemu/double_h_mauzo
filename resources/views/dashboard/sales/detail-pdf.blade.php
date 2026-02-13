<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Sales PDF</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        table, th, td { border: 1px solid black; padding: 5px; }
        th { background-color: #f0f0f0; }
        td { text-align: center; }
        td.text-left { text-align: left; }
    </style>
</head>
<body>
    <h4>Daily Item Sales Detail for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h4>
    <h5>{{ $shop->name ?? 'Double H Cosmetics Shop' }}</h5>

    <table>
        <thead>
            <tr>
                <th>S/N</th>
                <th>Item</th>
                <th>Quantity Sold</th>
                <th>Total (TZS)</th>
                <th>Sold by</th>
            
            </tr>
        </thead>
        <tbody>
            @foreach($itemRows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $row['product'] }}</td>
                    <td>{{ $row['quantity'] }}</td>
                    <td>{{ number_format($row['revenue'], 2) }}</td>
                    <td class="text-left">{{ $row['staff'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
