@php
    $money = fn ($v) => number_format((float) $v, 2);
    $number = \App\Helpers\InvoiceNumber::for($invoice);
    $owed = (float) $invoice->remaining_amount;
    $isCredit = ($invoice->payment_type ?? null) === 'credit';
    // $screen = shown in the browser (invoice page); otherwise rendered to PDF by DomPDF.
    $screen = $screen ?? false;
    $logoFile = public_path('assets/img/logo-pdf.png');
    $logo = $screen ? asset('assets/img/logo-pdf.png') : $logoFile;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice {{ $number }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; }
        @if($screen) body { margin: 0; padding: 28px 32px; background: #fff; } @endif
        .brand { color: #831619; }
        .head td { vertical-align: top; }
        .logo { width: 70px; }
        h1 { font-size: 20px; margin: 0; color: #831619; letter-spacing: 1px; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 10px; }
        .label { font-size: 9px; text-transform: uppercase; color: #6b7280; letter-spacing: .5px; }
        table { width: 100%; border-collapse: collapse; }
        .items th { background: #831619; color: #fff; padding: 6px; font-size: 10px; text-align: left; }
        .items td { border-bottom: 1px solid #eee; padding: 6px; }
        .items tr:nth-child(even) td { background: #fafafa; }
        .num { text-align: right; }
        .totals td { padding: 4px 6px; }
        .totals .grand td { font-size: 13px; font-weight: bold; border-top: 2px solid #831619; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .paid { background: #dcfce7; color: #166534; }
        .due { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width:80px">
                @if(file_exists($logoFile))<img src="{{ $logo }}" class="logo" alt="Double H Cosmetics">@endif
            </td>
            <td>
                <div style="font-size:15px; font-weight:bold;" class="brand">Double H Cosmetics</div>
                <div class="muted">{{ $invoice->shop->name ?? '' }}{{ ($invoice->shop->location ?? null) ? ' · ' . $invoice->shop->location : '' }}</div>
            </td>
            <td style="text-align:right">
                <h1>PURCHASE INVOICE</h1>
                <div style="font-size:13px; margin-top:4px;"><strong>{{ $number }}</strong></div>
                <div class="muted">{{ \Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y, H:i') }}</div>
                <div style="margin-top:4px;">
                    @if($isCredit && $owed > 0)
                        <span class="badge due">CREDIT · OWED Tsh {{ $money($owed) }}</span>
                    @else
                        <span class="badge paid">PAID</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table style="margin:16px 0;">
        <tr>
            <td style="width:50%; padding-right:6px;">
                <div class="box">
                    <div class="label">Supplier</div>
                    <div style="font-weight:bold; font-size:12px;">{{ $invoice->supplier->name ?? '—' }}</div>
                    @if($invoice->supplier->phone ?? null)<div>{{ $invoice->supplier->phone }}</div>@endif
                    @if($invoice->supplier->email ?? null)<div>{{ $invoice->supplier->email }}</div>@endif
                    @if($invoice->supplier->address ?? null)<div class="muted">{{ $invoice->supplier->address }}</div>@endif
                </div>
            </td>
            <td style="width:50%; padding-left:6px;">
                <div class="box">
                    <div class="label">Delivered to</div>
                    <div style="font-weight:bold; font-size:12px;">{{ $invoice->shop->name ?? '—' }}</div>
                    <div class="muted">{{ $invoice->shop->location ?? '' }}</div>
                    <div>Payment: {{ $isCredit ? 'Credit' : 'Cash' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:24px">#</th>
                <th>Product</th>
                <th style="width:60px">Unit</th>
                <th class="num" style="width:60px">Qty</th>
                <th class="num" style="width:95px">Unit price (Tsh)</th>
                <th class="num" style="width:105px">Amount (Tsh)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product->name ?? 'Deleted product' }}</td>
                    <td>{{ $item->product->unit->short_name ?? '—' }}</td>
                    <td class="num">{{ number_format($item->quantity) }}</td>
                    <td class="num">{{ $money($item->purchase_price) }}</td>
                    <td class="num">{{ $money($item->quantity * $item->purchase_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:55%"></td>
            <td>
                <table class="totals">
                    <tr><td class="muted">Items</td><td class="num">{{ number_format($invoice->items->sum('quantity')) }}</td></tr>
                    <tr class="grand"><td>Total</td><td class="num">Tsh {{ $money($invoice->total_amount) }}</td></tr>
                    <tr><td class="muted">Paid</td><td class="num">{{ $money($invoice->amount_paid) }}</td></tr>
                    <tr><td class="{{ $owed > 0 ? 'brand' : 'muted' }}">Balance owed</td><td class="num {{ $owed > 0 ? 'brand' : '' }}">{{ $money($owed) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by Double H Cosmetics on {{ now()->format('d M Y, H:i') }} · Thank you for your business.
    </div>
</body>
</html>
