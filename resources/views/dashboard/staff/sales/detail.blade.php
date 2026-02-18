<h5>Sales for {{ $date }}</h5>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total (TZS)</th>
            <th>Staff</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($itemRows as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item['product'] ?? 'Unknown' }}</td>
            <td>{{ $item['quantity'] ?? 0 }}</td>
            <td>{{ number_format($item['revenue'] ?? 0, 2) }}</td>
            <td>{{ $item['staff'] ?? 'Unknown' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted">No sales for this date.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<button class="btn btn-secondary btn-sm mt-2" onclick="document.getElementById('sales-details').style.display='none'; document.getElementById('sales-table-container').style.display='block';">
    ← Back to Sales
</button>
