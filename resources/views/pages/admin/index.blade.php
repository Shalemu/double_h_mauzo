@extends('main')

@section('title', 'Dashboard')

@section('content')

@include('pages.admin._index_styles')

<div class="container-fluid">

    <!-- Hero stat cards -->
    <div class="row g-4">

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('products.index', ['filter' => 'all']) }}" class="dashboard-card">
                <div class="dashboard-icon bg-blue"><i class="bi bi-box-seam"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number">{{ number_format($totalProducts) }}</div>
                        <div class="dashboard-title">Total Products</div>
                    </div>
                    <span class="dashboard-link text-primary">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('products.in-stock') }}" class="dashboard-card">
                <div class="dashboard-icon bg-green"><i class="bi bi-check-circle"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number">{{ number_format($remainingProducts) }}</div>
                        <div class="dashboard-title">Remaining In Stock</div>
                    </div>
                    <span class="dashboard-link text-success">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('products.expired') }}" class="dashboard-card">
                <div class="dashboard-icon bg-orange"><i class="bi bi-calendar-x"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number">{{ number_format($expiredProducts) }}</div>
                        <div class="dashboard-title">Expired</div>
                    </div>
                    <span class="dashboard-link text-warning">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('products.out-of-stock') }}" class="dashboard-card">
                <div class="dashboard-icon bg-red"><i class="bi bi-x-circle"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number">{{ number_format($outOfStockProducts) }}</div>
                        <div class="dashboard-title">Out of Stock</div>
                    </div>
                    <span class="dashboard-link text-danger">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

    </div>

    <!-- Overview chart + Top Selling Products -->
    <div class="row g-4 mt-1">

        <div class="col-xl-8">
            <div class="dashboard-panel">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="dashboard-panel-title mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Overview — {{ date('Y') }}</div>
                </div>
                <div class="d-flex gap-4 mb-3">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span style="width:10px;height:10px;border-radius:50%;background:#4f46e5;display:inline-block;"></span> Sales
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span style="width:10px;height:10px;border-radius:50%;background:#16a34a;display:inline-block;"></span> Purchases
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span style="width:10px;height:10px;border-radius:50%;background:#d97706;display:inline-block;"></span> Expenses
                    </div>
                </div>
                <div style="height:280px;">
                    <canvas id="overviewChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dashboard-panel">
                <div class="dashboard-panel-title"><i class="bi bi-trophy me-2 text-warning"></i>Top Selling Products</div>
                @forelse($topSellingProducts as $i => $p)
                    <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'mb-3' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <div class="dashboard-icon bg-blue" style="width:32px;height:32px;font-size:13px;font-weight:700;">{{ $i + 1 }}</div>
                            <div>
                                <div style="font-size:13.5px;font-weight:600;color:#1d2433;">{{ $p->name }}</div>
                                <div style="font-size:12px;color:#8891a3;">{{ $p->brand ?? 'No brand' }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:13.5px;font-weight:700;color:#1d2433;">{{ number_format($p->total_qty) }} sold</div>
                            <div style="font-size:12px;color:#8891a3;">TZS {{ number_format($p->total_revenue) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No sales recorded yet.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Quick Actions + secondary stats -->
    <div class="row g-4 mt-1">

        <div class="col-xl-4 col-lg-5">
            <div class="dashboard-panel">
                <div class="dashboard-panel-title">Quick Actions</div>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('admin.pos.index') }}" class="quick-action-btn">
                            <i class="bi bi-cash-coin"></i> Sell / POS
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('dashboard.shop') }}" class="quick-action-btn">
                            <i class="bi bi-shop"></i> Business Mgmt
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('products.index') }}" class="quick-action-btn">
                            <i class="bi bi-box-seam"></i> Manage Products
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('customers.index') }}" class="quick-action-btn">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="dashboard-panel">
                <div class="dashboard-panel-title">More Inventory Stats</div>
                <div class="row g-3">
                    @foreach([
                        ['products.running-out', 'bg-orange', 'bi-exclamation-triangle', $runningOutProducts, 'Running Out', 'text-warning'],
                        ['products.expiring', 'bg-blue', 'bi-calendar-event', $expiringProducts, 'Expiring within 1 year', 'text-primary'],
                        ['products.trash', 'bg-red', 'bi-trash', $disposedProducts, 'Disposed', 'text-danger'],
                    ] as [$route, $bg, $icon, $count, $label, $linkClass])
                        <div class="col-sm-4">
                            <a href="{{ route($route) }}" class="d-flex align-items-center gap-2 text-decoration-none">
                                <div class="dashboard-icon {{ $bg }}" style="width:40px;height:40px;font-size:16px;"><i class="bi {{ $icon }}"></i></div>
                                <div>
                                    <div class="dashboard-number" style="font-size:20px;">{{ number_format($count) }}</div>
                                    <div class="dashboard-title" style="margin-top:0;">{{ $label }}</div>
                                    <span class="dashboard-link small {{ $linkClass }}">View <i class="bi bi-arrow-right"></i></span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- Shop performance -->
    <div class="row g-4 mt-1">
        <div class="col-12">
    <div class="dashboard-panel p-3 h-100">
        <!-- Shop Tabs -->
        <ul class="nav nav-tabs mb-3" id="shopTab" role="tablist">
            @foreach($shops as $index => $shop)
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $index == 0 ? 'active' : '' }}"
                       id="shop{{ $shop->id }}-tab"
                       data-bs-toggle="tab"
                       href="#shop{{ $shop->id }}"
                       role="tab">{{ $shop->name }}</a>
                </li>
            @endforeach
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            @foreach($shops as $index => $shop)
                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}"
                     id="shop{{ $shop->id }}" role="tabpanel">
                    <table class="table table-bordered text-center">
                        <thead class="table-warning">
                            <tr>
                                <th>Metric</th>
                                <th>TZS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Purchases</td><td>{{ number_format($shop->totalPurchases, 2) }}</td></tr>
                            <tr><td>Sales</td><td>{{ number_format($shop->totalSales, 2) }}</td></tr>
                            <tr><td>Gross Profit</td><td>{{ number_format($shop->grossProfit, 2) }}</td></tr>
                            <tr><td>Total Expenses</td><td>{{ number_format($shop->totalExpenses, 2) }}</td></tr>
                            <tr><td>Net Profit</td><td>{{ number_format($shop->netProfit, 2) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
        </div>
    </div>

    <!-- Stock alerts -->
    @php
        $alertTabs = [
            'expired' => ['Expired', 'bi-calendar-x', 'text-danger', $expiredProducts, 'products.expired'],
            'running_out' => ['Running Out', 'bi-hourglass-split', 'text-warning', $runningOutProducts, 'products.running-out'],
            'expiring' => ['Expiring within 1 year', 'bi-calendar-event', 'text-primary', $expiringProducts, 'products.expiring'],
            'out_of_stock' => ['Out of Stock', 'bi-x-circle', 'text-secondary', $outOfStockProducts, 'products.out-of-stock'],
        ];
        $today = \Carbon\Carbon::today();
    @endphp
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            <div class="dashboard-panel p-3">
                <div class="dashboard-panel-title"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Stock Alerts</div>

                <ul class="nav nav-tabs mb-3 flex-nowrap overflow-auto" id="stockAlertTabs" role="tablist">
                    @foreach($alertTabs as $key => [$label, $icon, $color, $count, $route])
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-nowrap {{ $loop->first ? 'active' : '' }}" id="alert-{{ $key }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#alert-{{ $key }}" type="button" role="tab"
                                    aria-controls="alert-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <i class="bi {{ $icon }} {{ $color }} me-1"></i>{{ $label }}
                                <span class="badge rounded-pill bg-light text-dark border ms-1">{{ number_format($count) }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach($alertTabs as $key => [$label, $icon, $color, $count, $route])
                        @php $rows = $stockAlerts[$key]; @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="alert-{{ $key }}" role="tabpanel" aria-labelledby="alert-{{ $key }}-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center mb-2">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">Product</th>
                                            <th>Shop</th>
                                            <th>Qty</th>
                                            @if($key === 'running_out')<th>Minimum</th>@endif
                                            @if(in_array($key, ['expired', 'expiring']))<th>Expiry date</th><th>{{ $key === 'expired' ? 'Expired' : 'Time left' }}</th>@endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rows as $product)
                                            @php $expiry = $product->expire_date ? \Carbon\Carbon::parse($product->expire_date) : null; @endphp
                                            <tr>
                                                <td class="text-start">{{ $product->name }}</td>
                                                <td>{{ $product->shop->name ?? '—' }}</td>
                                                <td class="{{ $product->quantity <= 0 ? 'text-danger fw-semibold' : '' }}">{{ $product->quantity }}</td>
                                                @if($key === 'running_out')<td>{{ $product->min_quantity }}</td>@endif
                                                @if(in_array($key, ['expired', 'expiring']))
                                                    <td class="text-nowrap">{{ $expiry ? $expiry->format('d M Y') : '—' }}</td>
                                                    <td class="text-nowrap {{ $key === 'expired' ? 'text-danger' : ($expiry && $expiry->diffInDays($today) <= 30 ? 'text-danger fw-semibold' : '') }}">
                                                        {{ $expiry ? $expiry->diffForHumans(['parts' => 2]) : '—' }}
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-muted py-4">Nothing here — good news.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($count > 0)
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
                                    <span class="text-muted small">Showing {{ $rows->count() }} of {{ number_format($count) }}</span>
                                    <a href="{{ route($route) }}" class="btn btn-sm alert-view-all alert-view-{{ $key }}">
                                        View all {{ number_format($count) }} {{ strtolower($label) }}
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- START: page scripts -->
@include('pages.admin._index_scripts')
<!-- END: page scripts -->
<!-- @include('components/footer') -->

@endsection
