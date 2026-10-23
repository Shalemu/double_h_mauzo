<style>
.sms-kpi {
    --kpi:#831619; --kpi-soft:rgba(131,22,25,.09);
    display:flex; align-items:center; gap:.9rem; height:100%;
    background:#fff; border:1px solid #eef0f3; border-left:4px solid var(--kpi);
    border-radius:.85rem; padding:1rem 1.1rem; box-shadow:0 1px 3px rgba(15,23,42,.05);
    transition:transform .15s, box-shadow .15s;
}
a.sms-kpi:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(15,23,42,.08); }
.sms-kpi.kpi-blue  { --kpi:#2563eb; --kpi-soft:rgba(37,99,235,.09); }
.sms-kpi.kpi-green { --kpi:#16a34a; --kpi-soft:rgba(22,163,74,.1); }
.sms-kpi.kpi-red   { --kpi:#dc2626; --kpi-soft:rgba(220,38,38,.09); }
.sms-kpi.kpi-grey  { --kpi:#94a3b8; --kpi-soft:rgba(148,163,184,.14); }
.sms-kpi-icon {
    width:48px; height:48px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:var(--kpi-soft); color:var(--kpi); font-size:1.35rem;
}
.sms-kpi-label { font-size:.78rem; color:#6b7280; font-weight:500; }
.sms-kpi-value { font-size:1.6rem; font-weight:700; color:#111827; line-height:1.2; }
.sms-kpi-note { font-size:.75rem; color:#9ca3af; margin-top:.1rem; }
.sms-kpi.kpi-red .sms-kpi-value, .sms-kpi.kpi-red .sms-kpi-note { color:#dc2626; }
.sms-kpi-progress { height:5px; margin-top:.4rem; background:#f1f5f9; }
.sms-kpi-progress .progress-bar { background:var(--kpi); }
.sms-audience { display:flex; gap:.5rem; flex-wrap:wrap; }
.sms-audience label {
    flex:1; min-width:140px; border:1px solid #e5e7eb; border-radius:.65rem; padding:.6rem .8rem; cursor:pointer;
    display:flex; align-items:center; gap:.55rem; color:#374151; transition:.15s; background:#fff; margin:0;
}
.sms-audience label i { font-size:1.2rem; color:#9ca3af; }
.sms-audience .btn-check:checked + label { border-color:#831619; background:#fdf6f6; color:#831619; font-weight:600; }
.sms-audience .btn-check:checked + label i { color:#831619; }
.sms-audience .btn-check:focus-visible + label { outline:2px solid #831619; outline-offset:2px; }
.sms-people { max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:.6rem; }
.sms-people .form-check { padding:.45rem .75rem .45rem 2.2rem; border-bottom:1px solid #f3f4f6; margin:0; }
.sms-people .form-check:last-child { border-bottom:0; }
.sms-counter { font-size:.8rem; color:#6b7280; }
.sms-counter strong { color:#111827; }
.sms-phone { max-width:300px; margin:0 auto; border:10px solid #1f2937; border-radius:28px; background:#f3f4f6; min-height:360px; padding:16px 12px; }
.sms-phone-sender { text-align:center; font-size:.78rem; color:#6b7280; margin-bottom:10px; }
.sms-bubble { background:#fff; border-radius:16px 16px 16px 4px; padding:.65rem .8rem; font-size:.88rem; white-space:pre-wrap; word-break:break-word; box-shadow:0 1px 2px rgba(0,0,0,.08); }
.sms-template { font-size:.78rem; }
</style>
