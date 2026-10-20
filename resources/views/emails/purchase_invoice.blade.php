<!DOCTYPE html>
<html>
<body style="margin:0; padding:24px; background:#f6f6f7; font-family:Arial, sans-serif; color:#222;">
    <table role="presentation" width="100%" style="max-width:560px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden;">
        <tr>
            <td style="background:#831619; color:#fff; padding:18px 24px; font-size:18px; font-weight:bold;">
                Double H Cosmetics
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <p style="margin-top:0;">Hello {{ $invoice->supplier->name ?? 'Supplier' }},</p>
                <p>Please find attached our purchase invoice <strong>{{ $number }}</strong> for the goods delivered to
                    <strong>{{ $invoice->shop->name ?? 'our shop' }}</strong> on {{ \Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y') }}.</p>

                <table role="presentation" width="100%" style="border-collapse:collapse; margin:16px 0; font-size:14px;">
                    <tr><td style="padding:6px 0; color:#6b7280;">Items</td><td style="padding:6px 0; text-align:right;">{{ number_format($invoice->items->sum('quantity')) }}</td></tr>
                    <tr><td style="padding:6px 0; color:#6b7280;">Total</td><td style="padding:6px 0; text-align:right; font-weight:bold;">Tsh {{ number_format($invoice->total_amount, 2) }}</td></tr>
                    <tr><td style="padding:6px 0; color:#6b7280;">Paid</td><td style="padding:6px 0; text-align:right;">Tsh {{ number_format($invoice->amount_paid, 2) }}</td></tr>
                    <tr><td style="padding:6px 0; color:#6b7280;">Balance</td><td style="padding:6px 0; text-align:right;">Tsh {{ number_format($invoice->remaining_amount, 2) }}</td></tr>
                </table>

                <p style="margin-bottom:0;">Thank you,<br>Double H Cosmetics</p>
            </td>
        </tr>
    </table>
</body>
</html>
