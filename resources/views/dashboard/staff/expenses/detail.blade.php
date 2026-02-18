<h5>Expenses for {{ $date }}</h5>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>SN</th>
            <th>Title</th>
            <th>Amount (TZS)</th>
            <th>Note</th>
            <th>Added By</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($expenses as $expense)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $expense->title }}</td>
            <td>{{ number_format($expense->amount, 2) }}</td>
            <td>{{ $expense->note }}</td>
            <td>{{ $expense->user->name ?? 'Unknown' }}</td>
            <td>{{ $expense->created_at->format('H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-muted">No expenses for this date.</td>
        </tr>
        @endforelse
    </tbody>
</table>
