<!DOCTYPE html>
<html>
<head>
    <title>Products List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h3 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        td {
            vertical-align: top;
        }
    </style>
</head>
<body>
    <h3>Products List</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Min Quantity</th>
                <th>Purchase Price</th>
                <th>Selling Price</th>
                <th>Barcode</th>
                <th>Expire Date</th>
                <th>Size</th>
                <th>Color</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->brand ?? '-' }}</td>
                <td>{{ $product->category ? $product->category->name : '-' }}</td>
                <td>{{ $product->unit ? $product->unit->name : '-' }}</td>
                <td>{{ $product->quantity ?? 0 }}</td>
                <td>{{ $product->min_quantity ?? 0 }}</td>
                <td>{{ $product->purchase_price ?? 0 }}</td>
                <td>{{ $product->selling_price ?? 0 }}</td>
                <td>{{ $product->barcode ?? '-' }}</td>
                <td>{{ $product->expire_date ?? '-' }}</td>
                <td>{{ $product->size ?? '-' }}</td>
                <td>{{ $product->color ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
