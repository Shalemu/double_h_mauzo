@extends('main')

@section('title', 'Dashboard')

@section('content')

@include('components/breadcrumb')

<style>
    .premium-stat-card {
        border-width: 1px;
        border-radius: 1rem;
        transition: transform .18s ease, box-shadow .18s ease;
        background: #fff;
    }
    .premium-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.08) !important;
    }
    .premium-stat-icon {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        border-radius: 14px;
    }
    .premium-stat-label {
        letter-spacing: .04em;
        font-size: .72rem;
    }
    .premium-stat-value {
        font-size: 1.35rem;
    }

    /* Inventory Summary card */
    .premium-panel {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid rgba(0,0,0,.06);
        box-shadow: 0 .25rem 1rem rgba(0,0,0,.04);
    }
    .premium-panel-header {
        padding: 18px 20px;
        border-bottom: 1px solid rgba(0,0,0,.06);
        font-weight: 700;
        font-size: .95rem;
        color: #2d2d2d;
    }
    .premium-panel-header i { color: #1e88e5; margin-right: 8px; }
    .premium-panel-body { padding: 20px; }

    .mini-tile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-radius: .85rem;
        background: #f8f9fb;
        border: 1px solid rgba(0,0,0,.05);
        height: 100%;
        transition: all .18s ease;
    }
    .mini-tile:hover {
        background: #fff;
        box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.06);
        transform: translateY(-2px);
    }
    .mini-tile-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #fff;
    }
    .mini-tile-value {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.1;
        color: #2d2d2d;
    }
    .mini-tile-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #8a8f98;
        font-weight: 600;
    }
</style>

<div class="cat__content">

<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap" style="gap: 12px; padding-left: 50px;">
        <button type="button" class="btn btn-outline-danger">
            <i class="bi bi-cart-plus"></i> Sales
        </button>
        <button type="button" class="btn btn-outline-success">
            <i class="bi bi-bag-plus"></i> Purchases
        </button>
        <button type="button" class="btn btn-outline-warning text-dark">
            <i class="bi bi-cash-stack"></i> Expenses
        </button>
        <button type="button" class="btn btn-outline-primary">
            <i class="bi bi-shop"></i> My Shops
        </button>
        <button type="button" class="btn btn-outline-secondary">
            <i class="bi bi-geo-alt"></i> Salepoints
        </button>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-info text-dark">
            <i class="bi bi-people"></i> My Customers
        </a>
        <button type="button" class="btn btn-outline-warning text-dark">
            <i class="bi bi-person-badge"></i> My Employees
        </button>
    </div>
</div>

    <!-- Use container-fluid to reduce side gap -->
   <div class="container-fluid">
    <div class="row g-4" style="padding-left: 30px; padding-right: 30px;">

        <!-- Gross Profit -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-success w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-success text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Gross Profit (All Shops)</div>
                        <div class="fw-bold premium-stat-value text-success">Tz: {{ number_format($grossProfit, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-primary w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-primary text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Total Expenses (All Shops)</div>
                        <div class="fw-bold premium-stat-value text-primary">Tz: {{ number_format($totalExpenses, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-danger w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-danger text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-piggy-bank fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Net Profit</div>
                        <div class="fw-bold premium-stat-value text-danger">Tz: {{ number_format($netProfit, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-warning w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-warning text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-tags fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Sales (All Shops)</div>
                        <div class="fw-bold premium-stat-value text-warning-emphasis">Tz: {{ number_format($totalSales, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</div>


<div class="cat__content">
    <div class="container-fluid">
        <div class="row g-4" style="padding-left:30px; padding-right:30px;">

            <!-- Left: Inventory Summary -->
            <div class="col-xl-5">
                <div class="premium-panel h-100">
                    <div class="premium-panel-header">
                        <i class="bi bi-clipboard-data"></i> Inventory Summary
                    </div>
                    <div class="premium-panel-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="mini-tile">
                                    <div class="mini-tile-icon bg-primary"><i class="bi bi-box-seam"></i></div>
                                    <div>
                                        <div class="mini-tile-value">{{ number_format($totalProducts) }}</div>
                                        <div class="mini-tile-label">Total Products</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-tile">
                                    <div class="mini-tile-icon bg-success"><i class="bi bi-check-circle"></i></div>
                                    <div>
                                        <div class="mini-tile-value">{{ number_format($remainingProducts) }}</div>
                                        <div class="mini-tile-label">Remaining</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-tile">
                                    <div class="mini-tile-icon bg-danger"><i class="bi bi-calendar-x"></i></div>
                                    <div>
                                        <div class="mini-tile-value">{{ number_format($expiredProducts) }}</div>
                                        <div class="mini-tile-label">Expired</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-tile">
                                    <div class="mini-tile-icon bg-warning"><i class="bi bi-trash"></i></div>
                                    <div>
                                        <div class="mini-tile-value">{{ number_format($disposedProducts) }}</div>
                                        <div class="mini-tile-label">Disposed</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-tile">
                                    <div class="mini-tile-icon bg-info"><i class="bi bi-exclamation-triangle"></i></div>
                                    <div>
                                        <div class="mini-tile-value">{{ number_format($runningOutProducts) }}</div>
                                        <div class="mini-tile-label">Running Out</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-tile">
                                    <div class="mini-tile-icon bg-dark"><i class="bi bi-x-circle"></i></div>
                                    <div>
                                        <div class="mini-tile-value">{{ number_format($outOfStockProducts) }}</div>
                                        <div class="mini-tile-label">Out of Stock</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Shop Tables -->
        <div class="col-xl-7">
    <div class="cat__core__widget p-3 h-100" style="background:#fff;">
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
    </div>
</div><br>


<!-- START: page scripts -->
<script>
    $( function() {

        ///////////////////////////////////////////////////////////
        // tooltips
        $("[data-toggle=tooltip]").tooltip();

        ///////////////////////////////////////////////////////////
        // chart1
        new Chartist.Line(".chart-line", {
            labels: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
            series: [
                [5, 0, 7, 8, 12],
                [2, 1, 3.5, 7, 3],
                [1, 3, 4, 5, 6]
            ]
        }, {
            fullWidth: !0,
            chartPadding: {
                right: 40
            },
            plugins: [
                Chartist.plugins.tooltip()
            ]
        });

        ///////////////////////////////////////////////////////////
        // chart 2
        var overlappingData = {
                    labels: ["Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    series: [
                        [5, 4, 3, 7, 5, 10, 3, 4, 8, 10, 6, 8],
                        [3, 2, 9, 5, 4, 6, 4, 6, 7, 8, 7, 4]
                    ]
                },
                overlappingOptions = {
                    seriesBarDistance: 10,
                    plugins: [
                        Chartist.plugins.tooltip()
                    ]
                },
                overlappingResponsiveOptions = [
                    ["", {
                        seriesBarDistance: 5,
                        axisX: {
                            labelInterpolationFnc: function(value) {
                                return value[0]
                            }
                        }
                    }]
                ];

        new Chartist.Bar(".chart-overlapping-bar", overlappingData, overlappingOptions, overlappingResponsiveOptions);

        ///////////////////////////////////////////////////////////
        // custom scroll
        if (!('ontouchstart' in document.documentElement) && jQuery().jScrollPane) {
            $('.custom-scroll').each(function() {
                $(this).jScrollPane({
                    contentWidth: '100%',
                    autoReinitialise: true,
                    autoReinitialiseDelay: 100
                });
                var api = $(this).data('jsp'),
                        throttleTimeout;
                $(window).bind('resize', function() {
                    if (!throttleTimeout) {
                        throttleTimeout = setTimeout(function() {
                            api.reinitialise();
                            throttleTimeout = null;
                        }, 50);
                    }
                });
            });
        }

    } );
</script>
<!-- END: page scripts -->
<!-- @include('components/footer') -->

@endsection
