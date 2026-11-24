@extends('main')

@section('title', 'POS — Choose Shop')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="border rounded-4 shadow-sm bg-white p-4" style="max-width: 1100px; margin: auto;">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom">
                    <h4 class="mb-0"><i class="bi bi-cart-check me-2 text-success"></i>POS — Choose a Shop</h4>
                    <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <p class="text-muted mb-4">
                    Pick which shop you're selling for. Stock, prices, and the sale record will all belong to that shop.
                </p>

                @if($shops->isEmpty())
                    <div class="alert alert-warning">No shops exist yet. Create a shop first.</div>
                @else
                    <div class="row g-3">
                        @foreach($shops as $shop)
                            @php $suspended = $shop->isSuspended(); @endphp
                            <div class="col-lg-4 col-sm-6">
                                <a href="{{ route('admin.pos.index', ['shop' => $shop->id]) }}"
                                   class="text-decoration-none d-block h-100" aria-label="Open POS for {{ $shop->name }}">
                                    <div class="card h-100 shop-pick-card {{ $suspended ? 'is-suspended' : '' }}">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-start gap-3 mb-3">
                                                <div class="shop-pick-icon" aria-hidden="true">
                                                    <i class="bi bi-shop"></i>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <h6 class="fw-bold mb-1 text-truncate" title="{{ $shop->name }}">{{ $shop->name }}</h6>
                                                    <div class="small text-muted text-truncate">
                                                        <i class="bi bi-geo-alt me-1"></i>{{ $shop->location ?: 'No location set' }}
                                                    </div>
                                                </div>
                                                @if($suspended)
                                                    <span class="badge bg-warning text-dark">Suspended</span>
                                                @endif
                                            </div>

                                            <div class="d-flex gap-2 mb-3">
                                                <div class="shop-pick-stat">
                                                    <i class="bi bi-box-seam"></i>
                                                    <span><strong>{{ number_format($shop->products_count ?? 0) }}</strong> products</span>
                                                </div>
                                                <div class="shop-pick-stat">
                                                    <i class="bi bi-receipt"></i>
                                                    <span><strong>{{ number_format($shop->sales_today_count ?? 0) }}</strong> sales today</span>
                                                </div>
                                            </div>

                                            <div class="mt-auto shop-pick-cta">
                                                Open POS <i class="bi bi-arrow-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

@include('pages.admin._pos_select_shop_styles')

@endsection
