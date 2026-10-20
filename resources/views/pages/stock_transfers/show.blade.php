@extends('main')

@section('title', 'Stock Transfer #' . $transfer->id)

@section('content')
<style>
.transfer-meta span { display:block; font-size:.75rem; color:#6b7280; }
.transfer-meta strong { font-size:.95rem; }
@media print {
    .app-header, .app-sidebar, .app-footer, .app-content-header, .no-print { display:none !important; }
    .app-main, .app-wrapper { margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:1px solid #ccc !important; }
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-end flex-wrap gap-2 mb-3 no-print">
        <a href="{{ route('stock-transfers.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to history</a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Transfer #{{ $transfer->id }}</h5>
            <span class="badge bg-success">Completed</span>
        </div>
        <div class="card-body">
            <div class="row g-3 transfer-meta mb-4">
                <div class="col-sm-6 col-lg-3"><span>Date</span><strong>{{ $transfer->created_at->format('d M Y H:i') }}</strong></div>
                <div class="col-sm-6 col-lg-3"><span>From</span><strong>{{ $transfer->fromShop->label ?? '—' }}</strong></div>
                <div class="col-sm-6 col-lg-3"><span>To</span><strong>{{ $transfer->toShop->label ?? '—' }}</strong></div>
                <div class="col-sm-6 col-lg-3"><span>Transferred by</span><strong>{{ $transfer->transferredBy->name ?? '—' }}</strong></div>
                @if($transfer->note)
                    <div class="col-12"><span>Note</span><strong>{{ $transfer->note }}</strong></div>
                @endif
            </div>

            @php $totalUnits = 0; $totalValue = 0; @endphp
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th><th class="text-start">Product</th><th>Barcode</th><th>Unit</th><th>Quantity</th>
                            <th>Purchasing (TZS)</th><th>Retail (TZS)</th><th>Wholesale (TZS)</th><th>Value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfer->items as $item)
                            @php
                                $product = $products->get($item->from_product_id);
                                $value = $product ? $item->quantity * $product->purchase_price : null;
                                $totalUnits += $item->quantity;
                                $totalValue += $value ?? 0;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td class="text-center">{{ $product->barcode ?? '—' }}</td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $product->unit->short_name ?? '—' }}</span></td>
                                <td class="text-center fw-semibold">{{ $item->quantity }}{{ $product && $product->unit ? ' ' . $product->unit->short_name : '' }}</td>
                                <td class="text-end">{{ $product ? number_format($product->purchase_price, 2) : '—' }}</td>
                                <td class="text-end">{{ $product ? number_format($product->selling_price, 2) : '—' }}</td>
                                <td class="text-end">{{ $product ? number_format($product->wholesale_price, 2) : '—' }}</td>
                                <td class="text-end">{{ $value !== null ? number_format($value, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">Total</td>
                            <td class="text-center">{{ $totalUnits }}</td>
                            <td colspan="3"></td>
                            <td class="text-end">{{ number_format($totalValue, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="small text-muted mt-2 mb-0">Prices are the sending product's current prices. "—" means that product has since been deleted.</p>
        </div>
    </div>
</div>

@if(request()->boolean('print'))
    <script>window.addEventListener('load', () => window.print());</script>
@endif
@endsection
