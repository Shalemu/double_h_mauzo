@extends('main')

@section('title', 'Revenue & Profit')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        @foreach([
            ['Sales', $totals['sales'], ''],
            ['Returns (already deducted)', $totals['returns'], 'text-warning'],
            ['Gross profit', $totals['gross_profit'], $totals['gross_profit'] < 0 ? 'text-danger' : 'text-success'],
            ['Net profit', $totals['net_profit'], $totals['net_profit'] < 0 ? 'text-danger' : 'text-success'],
        ] as [$label, $value, $class])
            <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">{{ $label }} (Tsh)</div>
                <div class="fs-4 fw-bold {{ $class }}">{{ number_format($value, 2) }}</div>
            </div></div></div>
        @endforeach
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Revenue by Shop</h5>
            <small class="text-muted">Sales are already net of returns. Gross profit = sales − cost of goods; net profit also subtracts expenses recorded in the period.</small>
        </div>
        <div class="card-body">
            @include('pages.sales_revenue._filters')

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-end mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Shop</th>
                            <th>Sales</th>
                            <th>Returns<br><small class="fw-normal">(already deducted)</small></th>
                            <th>Cost of goods</th>
                            <th>Gross profit</th>
                            <th>Expenses</th>
                            <th>Net profit</th>
                            <th>Fixed expenses<br><small class="fw-normal">(monthly, not deducted)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="text-start">
                                    <a href="{{ route('dashboard.shop.show', $row['shop']->id) }}">{{ $row['shop']->name }}</a>
                                </td>
                                <td>{{ number_format($row['sales'], 2) }}</td>
                                <td>{{ number_format($row['returns'], 2) }}</td>
                                <td>{{ number_format($row['cost'], 2) }}</td>
                                <td class="{{ $row['gross_profit'] < 0 ? 'text-danger' : '' }}">{{ number_format($row['gross_profit'], 2) }}</td>
                                <td><a href="{{ route('expenses.index', $row['shop']->id) }}">{{ number_format($row['expenses'], 2) }}</a></td>
                                <td class="fw-semibold {{ $row['net_profit'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($row['net_profit'], 2) }}</td>
                                <td><a href="{{ route('fixed-expenses.index', $row['shop']->id) }}">{{ number_format($row['fixed'], 2) }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No shops found.</td></tr>
                        @endforelse
                    </tbody>
                    @if($rows->count() > 1)
                        <tfoot class="fw-bold">
                            <tr>
                                <td class="text-start">Total</td>
                                <td>{{ number_format($totals['sales'], 2) }}</td>
                                <td>{{ number_format($totals['returns'], 2) }}</td>
                                <td>{{ number_format($totals['cost'], 2) }}</td>
                                <td>{{ number_format($totals['gross_profit'], 2) }}</td>
                                <td>{{ number_format($totals['expenses'], 2) }}</td>
                                <td>{{ number_format($totals['net_profit'], 2) }}</td>
                                <td>{{ number_format($totals['fixed'], 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Daily sales</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered text-center mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Sales</th><th>Total (Tsh)</th></tr></thead>
                    <tbody>
                        @forelse($daily as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->day)->format('D, d M Y') }}</td>
                                <td>{{ $day->count }}</td>
                                <td>{{ number_format($day->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted py-3">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
