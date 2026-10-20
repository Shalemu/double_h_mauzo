@extends('main')

@section('title', 'SMS')

@section('content')
@php
    $canSend = auth()->user()->hasPermission('sms.send');
    $people = [
        'customers' => $customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'shop_id' => $c->shop_id, 'shop' => $c->shop->name ?? '—'])->values(),
        'staff' => $staff->map(fn ($s) => ['id' => $s->id, 'name' => trim($s->first_name . ' ' . $s->last_name), 'phone' => $s->phone, 'shop_id' => $s->shop_id, 'shop' => $s->shop->name ?? '—'])->values(),
    ];
@endphp
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

<div class="container-fluid py-4">
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    @unless($configured)
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i> SMS is not configured. Add <code>MSHASTRA_USER</code>, <code>MSHASTRA_PASSWORD</code>, <code>MSHASTRA_SENDERID</code> and <code>MSHASTRA_JSON_URL</code> to <code>.env</code>.</div>
    @endunless

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="sms-kpi kpi-brand">
                <div class="sms-kpi-icon"><i class="bi bi-send-check"></i></div>
                <div class="sms-kpi-body">
                    <div class="sms-kpi-label">SMS sent today</div>
                    <div class="sms-kpi-value">{{ number_format($stats['today']) }}</div>
                    <div class="sms-kpi-note">{{ $stats['today_people'] ? 'to ' . number_format($stats['today_people']) . ' ' . \Illuminate\Support\Str::plural('person', $stats['today_people']) : 'Nothing sent yet today' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="sms-kpi kpi-blue">
                <div class="sms-kpi-icon"><i class="bi bi-calendar3"></i></div>
                <div class="sms-kpi-body">
                    <div class="sms-kpi-label">SMS sent in {{ now()->format('F') }}</div>
                    <div class="sms-kpi-value">{{ number_format($stats['month']) }}</div>
                    <div class="sms-kpi-note">{{ $stats['month_messages'] ? number_format($stats['month_messages']) . ' ' . \Illuminate\Support\Str::plural('message', $stats['month_messages']) . ' delivered' : 'No messages yet this month' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="sms-kpi kpi-green">
                <div class="sms-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="sms-kpi-body w-100">
                    <div class="sms-kpi-label">Success rate this month</div>
                    <div class="sms-kpi-value">{{ $stats['rate'] !== null ? $stats['rate'] . '%' : '—' }}</div>
                    <div class="progress sms-kpi-progress" role="progressbar" aria-label="Success rate" aria-valuenow="{{ $stats['rate'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: {{ $stats['rate'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('sms.index', ['tab' => 'history', 'status' => 'failed']) }}" class="sms-kpi {{ $stats['failed'] ? 'kpi-red' : 'kpi-grey' }} text-decoration-none">
                <div class="sms-kpi-icon"><i class="bi {{ $stats['failed'] ? 'bi-exclamation-triangle' : 'bi-shield-check' }}"></i></div>
                <div class="sms-kpi-body">
                    <div class="sms-kpi-label">Failed today</div>
                    <div class="sms-kpi-value">{{ number_format($stats['failed']) }}</div>
                    <div class="sms-kpi-note">
                        @if($stats['failed'])
                            View failed messages <i class="bi bi-arrow-right"></i>
                        @elseif($stats['last_failed'])
                            Last failure {{ \Carbon\Carbon::parse($stats['last_failed'])->diffForHumans() }}
                        @else
                            No failures — all good
                        @endif
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>SMS</h5>
                    <small class="text-muted">Send messages to your customers and staff. Sender ID: <strong>{{ $sender ?: '—' }}</strong></small>
                </div>
            </div>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item"><a class="nav-link {{ $tab === 'compose' ? 'active' : '' }}" href="{{ route('sms.index') }}"><i class="bi bi-pencil-square me-1"></i> Compose</a></li>
                <li class="nav-item"><a class="nav-link {{ $tab === 'history' ? 'active' : '' }}" href="{{ route('sms.index', ['tab' => 'history']) }}"><i class="bi bi-clock-history me-1"></i> History <span class="badge bg-light text-dark border ms-1">{{ number_format($logs->total()) }}</span></a></li>
            </ul>
        </div>

        <div class="card-body">
            @if($tab === 'compose')
                @if(!$canSend)
                    <div class="alert alert-info mb-0">You can view SMS history, but you don't have permission to send SMS.</div>
                @else
                <form method="POST" action="{{ route('sms.send') }}" id="smsForm">
                    @csrf
                    <div class="row g-4">
                        <div class="col-lg-7">
                            {{-- 1. Who --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">1. Send to</label>
                                <div class="sms-audience" role="radiogroup" aria-label="Send to">
                                    @foreach(['customers' => ['bi-people', 'Customers', $customers->count()], 'staff' => ['bi-person-badge', 'Staff', $staff->count()], 'custom' => ['bi-telephone-plus', 'Other numbers', null]] as $key => [$icon, $label, $count])
                                        <input type="radio" class="btn-check" name="audience" id="aud-{{ $key }}" value="{{ $key }}" {{ old('audience', 'customers') === $key ? 'checked' : '' }}>
                                        <label for="aud-{{ $key }}"><i class="bi {{ $icon }}"></i> <span>{{ $label }}@if($count !== null) <small class="text-muted fw-normal">({{ $count }})</small>@endif</span></label>
                                    @endforeach
                                </div>
                            </div>

                            <div id="peopleBox">
                                <div class="row g-2 mb-2 align-items-end">
                                    <div class="col-sm-5">
                                        <label class="form-label small mb-1" for="smsShop">Shop / Store</label>
                                        <select name="shop_id" id="smsShop" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            @foreach($shops as $shop)
                                                <option value="{{ $shop->id }}" {{ (string) old('shop_id') === (string) $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="btn-group btn-group-sm w-100" role="radiogroup" aria-label="Recipients">
                                            <input type="radio" class="btn-check" name="scope" id="scopeAll" value="all" {{ old('scope', 'all') === 'all' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary" for="scopeAll">Everyone listed</label>
                                            <input type="radio" class="btn-check" name="scope" id="scopeSelected" value="selected" {{ old('scope') === 'selected' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary" for="scopeSelected">Choose people</label>
                                        </div>
                                    </div>
                                </div>
                                <div id="choosePeople" hidden>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="search" id="peopleSearch" class="form-control form-control-sm" placeholder="Search name or phone..." aria-label="Search people">
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" id="toggleAllPeople">Select all</button>
                                    </div>
                                    <div class="sms-people" id="peopleList"></div>
                                </div>
                            </div>

                            <div id="customBox" hidden>
                                <label class="form-label small mb-1" for="customNumbers">Phone numbers <span class="text-muted">(one per line, or separated by commas)</span></label>
                                <textarea name="custom_numbers" id="customNumbers" class="form-control" rows="3" placeholder="0767983236&#10;0712345678">{{ old('custom_numbers') }}</textarea>
                            </div>

                            {{-- 2. Message --}}
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-end mb-1">
                                    <label class="form-label fw-semibold mb-0" for="smsMessage">2. Message</label>
                                    <button type="button" class="btn btn-sm btn-link p-0" id="insertName" title="Each person sees their own first name">Insert {name}</button>
                                </div>
                                <textarea name="message" id="smsMessage" class="form-control" rows="5" maxlength="918" required placeholder="Type your message...">{{ old('message') }}</textarea>
                                <div class="d-flex flex-wrap justify-content-between gap-2 mt-1 sms-counter">
                                    <span><strong id="cChars">0</strong> characters · <strong id="cParts">0</strong> SMS each</span>
                                    <span><strong id="cRecipients">0</strong> recipients · <strong id="cTotal">0</strong> SMS total</span>
                                </div>
                                {{-- Ready-made messages, in English or Kiswahili (filled in by the script below). --}}
                                <div class="sms-templates mt-3">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <span class="small text-muted">Templates in</span>
                                        <div class="btn-group btn-group-sm" role="radiogroup" aria-label="Template language">
                                            <input type="radio" class="btn-check" name="tpl_lang" id="langSw" value="sw" checked>
                                            <label class="btn btn-outline-primary" for="langSw">Kiswahili</label>
                                            <input type="radio" class="btn-check" name="tpl_lang" id="langEn" value="en">
                                            <label class="btn btn-outline-primary" for="langEn">English</label>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mb-2" id="generalTemplates"></div>
                                    <div class="row g-2 align-items-center">
                                        <div class="col-sm-8">
                                            <select id="holidayTemplate" class="form-select form-select-sm" aria-label="Holiday wishes">
                                                <option value="">🎉 Holiday wishes…</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 small text-muted" id="nextHoliday"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary px-4" id="smsReview" {{ $configured ? '' : 'disabled' }}><i class="bi bi-send me-1"></i> Send SMS</button>
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div class="col-lg-5">
                            <div class="small text-muted text-center mb-2">Preview <span id="previewWho"></span></div>
                            <div class="sms-phone">
                                <div class="sms-phone-sender"><i class="bi bi-person-circle"></i> Double H Cosmetics</div>
                                <div class="sms-bubble" id="smsPreview"><span class="text-muted">Your message will appear here.</span></div>
                            </div>
                            @if($sender && !\Illuminate\Support\Str::contains(strtolower($sender), 'double'))
                                <p class="small text-muted text-center mt-2 mb-0">
                                    <i class="bi bi-info-circle"></i> Phones show the sender as <strong>{{ $sender }}</strong> until a Double H sender ID is registered with Mobishastra.
                                </p>
                            @endif
                        </div>
                    </div>
                </form>
                @endif
            @else
                <form method="GET" class="row g-2 align-items-end mb-3">
                    <input type="hidden" name="tab" value="history">
                    <div class="col-sm-4 col-md-3">
                        <select name="status" class="form-select form-select-sm" aria-label="Status">
                            <option value="">All statuses</option>
                            <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-sm-5 col-md-4">
                        <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name, number or text..." aria-label="Search">
                    </div>
                    <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr><th>Date</th><th>To</th><th>Type</th><th class="text-start">Message</th><th>SMS</th><th>Status</th><th>Sent by</th></tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="text-nowrap small">{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td class="text-nowrap">{{ $log->receiver ?: '—' }}<div class="small text-muted">{{ $log->recipient }}</div></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ ucfirst($log->type) }}</span></td>
                                    <td class="small" style="max-width:420px">{{ $log->message }}</td>
                                    <td class="text-center">{{ $log->sms_count }}</td>
                                    <td class="text-center">
                                        @if($log->status === 'sent')
                                            <span class="badge bg-success">Sent</span>
                                        @else
                                            <span class="badge bg-danger" title="{{ $log->response['body'] ?? '' }}">Failed{{ ($log->response['error_type'] ?? null) ? ' · ' . $log->response['error_type'] : '' }}</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $log->sentBy->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No SMS sent yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $logs->links('pagination::bootstrap-4') }}</div>
            @endif
        </div>
    </div>
</div>

@if($tab === 'compose' && $canSend)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const people = @json($people);
    const oldIds = @json(array_map('intval', (array) old('ids', [])));
    const form = document.getElementById('smsForm');
    const shop = document.getElementById('smsShop');
    const peopleBox = document.getElementById('peopleBox');
    const customBox = document.getElementById('customBox');
    const choose = document.getElementById('choosePeople');
    const list = document.getElementById('peopleList');
    const search = document.getElementById('peopleSearch');
    const message = document.getElementById('smsMessage');
    const custom = document.getElementById('customNumbers');
    const preview = document.getElementById('smsPreview');
    const selected = new Set(oldIds);

    const audience = () => form.querySelector('[name=audience]:checked').value;
    const scope = () => form.querySelector('[name=scope]:checked').value;
    const firstName = n => (n || '').trim().split(/\s+/)[0].replace(/^./, c => c.toUpperCase()).replace(/(?<=.)./g, c => c.toLowerCase());

    function inShop() {
        const a = audience();
        if (a === 'custom') return [];
        return people[a].filter(p => !shop.value || String(p.shop_id) === shop.value);
    }

    function recipients() {
        if (audience() === 'custom') {
            return custom.value.split(/[\s,;]+/).filter(Boolean).map(n => ({ name: null, phone: n }));
        }
        const pool = inShop();
        return scope() === 'all' ? pool : pool.filter(p => selected.has(p.id));
    }

    function renderList() {
        const term = search.value.trim().toLowerCase();
        list.replaceChildren();
        const pool = inShop().filter(p => !term || p.name.toLowerCase().includes(term) || String(p.phone).includes(term));
        if (!pool.length) {
            const empty = document.createElement('div');
            empty.className = 'p-3 small text-muted text-center';
            empty.textContent = 'No one with a phone number here.';
            list.appendChild(empty);
        }
        pool.forEach(function (p) {
            const wrap = document.createElement('div');
            wrap.className = 'form-check';
            const box = document.createElement('input');
            box.type = 'checkbox';
            box.className = 'form-check-input';
            box.name = 'ids[]';
            box.value = p.id;
            box.id = 'p-' + audience() + '-' + p.id;
            box.checked = selected.has(p.id);
            box.addEventListener('change', () => { box.checked ? selected.add(p.id) : selected.delete(p.id); update(); });
            const label = document.createElement('label');
            label.className = 'form-check-label d-flex justify-content-between w-100';
            label.htmlFor = box.id;
            const name = document.createElement('span');
            name.textContent = p.name;
            const meta = document.createElement('small');
            meta.className = 'text-muted';
            meta.textContent = p.phone + ' · ' + p.shop;
            label.append(name, meta);
            wrap.append(box, label);
            list.appendChild(wrap);
        });
    }

    function update() {
        const a = audience();
        peopleBox.hidden = a === 'custom';
        customBox.hidden = a !== 'custom';
        custom.required = a === 'custom';
        choose.hidden = a === 'custom' || scope() !== 'selected';

        const rec = recipients();
        const text = message.value;
        const parts = text.length ? Math.ceil(text.length / 160) : 0;
        document.getElementById('cChars').textContent = text.length;
        document.getElementById('cParts').textContent = parts;
        document.getElementById('cRecipients').textContent = rec.length;
        document.getElementById('cTotal').textContent = parts * rec.length;

        const sample = rec.find(r => r.name);
        document.getElementById('previewWho').textContent = sample ? 'as ' + sample.name + ' sees it' : '';
        if (text.trim()) {
            preview.textContent = text.replaceAll('{name}', sample ? firstName(sample.name) : 'Customer');
        } else {
            preview.innerHTML = '<span class="text-muted">Your message will appear here.</span>';
        }
    }

    form.querySelectorAll('[name=audience]').forEach(r => r.addEventListener('change', () => { selected.clear(); search.value = ''; renderList(); update(); }));
    form.querySelectorAll('[name=scope]').forEach(r => r.addEventListener('change', () => { renderList(); update(); }));
    shop.addEventListener('change', () => { renderList(); update(); });
    search.addEventListener('input', renderList);
    [message, custom].forEach(el => el.addEventListener('input', update));

    document.getElementById('toggleAllPeople').addEventListener('click', function () {
        const boxes = Array.from(list.querySelectorAll('input[type=checkbox]'));
        const allOn = boxes.length && boxes.every(b => b.checked);
        boxes.forEach(b => { b.checked = !allOn; const id = parseInt(b.value, 10); allOn ? selected.delete(id) : selected.add(id); });
        update();
    });

    document.getElementById('insertName').addEventListener('click', function () {
        const pos = message.selectionStart ?? message.value.length;
        message.value = message.value.slice(0, pos) + '{name}' + message.value.slice(message.selectionEnd ?? pos);
        message.focus();
        update();
    });
    // ---- Templates (Kiswahili / English) ----
    const templates = {
        general: [
            { label: { sw: 'Asante', en: 'Thank you' },
              sw: 'Habari {name}, asante kwa kununua Double H Cosmetics! Tunakuthamini sana, karibu tena.',
              en: 'Hello {name}, thank you for shopping with Double H Cosmetics! We appreciate you.' },
            { label: { sw: 'Mzigo mpya', en: 'New stock' },
              sw: 'Habari {name}, mzigo mpya umewasili Double H Cosmetics. Karibu dukani leo upate bidhaa mpya!',
              en: 'Hello {name}, new stock has arrived at Double H Cosmetics. Visit us today for the latest products!' },
            { label: { sw: 'Ukumbusho wa deni', en: 'Payment reminder' },
              sw: 'Habari {name}, tunakukumbusha kwa upole kuhusu salio la deni lako Double H Cosmetics. Asante.',
              en: 'Hello {name}, this is a friendly reminder about your outstanding balance at Double H Cosmetics. Thank you.' },
            { label: { sw: 'Ofa', en: 'Offer' },
              sw: 'Habari {name}, Double H Cosmetics tuna ofa maalum leo! Karibu dukani upate punguzo kwenye bidhaa zetu.',
              en: 'Hello {name}, Double H Cosmetics has a special offer today! Visit us for discounts on our products.' },
            { label: { sw: 'Taarifa kwa wafanyakazi', en: 'Staff notice' },
              sw: 'Habari {name}, tafadhali fahamu kwamba: ',
              en: 'Hello {name}, please note: ' },
        ],
        // month/day for fixed-date holidays; movable ones (Eid, Easter, Maulid) have no date.
        holidays: [
            { m: 1, d: 1, sw_name: 'Mwaka Mpya', en_name: 'New Year',
              sw: 'Heri ya Mwaka Mpya {name}! Double H Cosmetics inakutakia mwaka wenye afya, furaha na mafanikio. Asante kwa kuwa nasi.',
              en: 'Happy New Year {name}! Double H Cosmetics wishes you a healthy, happy and successful year. Thank you for being with us.' },
            { m: 1, d: 12, sw_name: 'Mapinduzi ya Zanzibar', en_name: 'Zanzibar Revolution Day',
              sw: 'Heri ya Sikukuu ya Mapinduzi ya Zanzibar {name}! Double H Cosmetics inakutakia sikukuu njema.',
              en: 'Happy Zanzibar Revolution Day {name}! Best wishes from Double H Cosmetics.' },
            { m: 4, d: 7, sw_name: 'Siku ya Karume', en_name: 'Karume Day',
              sw: '{name}, katika Siku ya Karume, Double H Cosmetics inaungana nawe kumkumbuka Hayati Sheikh Abeid Amani Karume.',
              en: '{name}, on Karume Day, Double H Cosmetics joins you in remembering the late Sheikh Abeid Amani Karume.' },
            { m: 4, d: 26, sw_name: 'Muungano', en_name: 'Union Day',
              sw: 'Heri ya Sikukuu ya Muungano {name}! Double H Cosmetics inakutakia sikukuu njema. Tudumishe Muungano wetu.',
              en: 'Happy Union Day {name}! Double H Cosmetics wishes you a wonderful Union Day.' },
            { m: 5, d: 1, sw_name: 'Mei Mosi', en_name: "Workers' Day",
              sw: 'Heri ya Mei Mosi {name}! Double H Cosmetics inakutakia Siku njema ya Wafanyakazi. Asante kwa juhudi zako.',
              en: "Happy Workers' Day {name}! Double H Cosmetics thanks you for your hard work." },
            { m: 7, d: 7, sw_name: 'Saba Saba', en_name: 'Saba Saba',
              sw: 'Heri ya Sikukuu ya Saba Saba {name}! Double H Cosmetics inakutakia siku njema.',
              en: 'Happy Saba Saba {name}! Best wishes from Double H Cosmetics.' },
            { m: 8, d: 8, sw_name: 'Nane Nane', en_name: 'Nane Nane (Farmers Day)',
              sw: 'Heri ya Sikukuu ya Nane Nane {name}! Double H Cosmetics inawatakia wakulima na wafugaji sikukuu njema.',
              en: 'Happy Nane Nane {name}! Double H Cosmetics wishes all farmers a great Farmers Day.' },
            { m: 10, d: 14, sw_name: 'Siku ya Nyerere', en_name: 'Nyerere Day',
              sw: '{name}, katika Siku ya Kumbukumbu ya Mwalimu Julius K. Nyerere, Double H Cosmetics inaungana nawe kumuenzi Baba wa Taifa. Tudumishe umoja na amani.',
              en: '{name}, on Nyerere Day Double H Cosmetics joins you in honouring Mwalimu Julius K. Nyerere, the Father of the Nation. Let us keep unity and peace.' },
            { m: 12, d: 9, sw_name: 'Uhuru', en_name: 'Independence Day',
              sw: 'Heri ya Sikukuu ya Uhuru {name}! Double H Cosmetics inakutakia sikukuu njema ya Uhuru wa Tanzania.',
              en: 'Happy Independence Day {name}! Double H Cosmetics wishes you a joyful Tanzania Independence Day.' },
            { m: 12, d: 25, sw_name: 'Krismasi', en_name: 'Christmas',
              sw: 'Heri ya Krismasi {name}! Double H Cosmetics inakutakia wewe na familia yako sikukuu njema yenye amani na furaha.',
              en: 'Merry Christmas {name}! Double H Cosmetics wishes you and your family a peaceful and joyful Christmas.' },
            { m: 12, d: 26, sw_name: 'Siku ya Kufungua Zawadi', en_name: 'Boxing Day',
              sw: 'Heri ya Siku ya Kufungua Zawadi {name}! Double H Cosmetics inakutakia sikukuu njema. Karibu dukani!',
              en: 'Happy Boxing Day {name}! Best wishes from Double H Cosmetics. Visit us for holiday offers!' },
            { sw_name: 'Eid al-Fitr', en_name: 'Eid al-Fitr',
              sw: 'Eid Mubarak {name}! Double H Cosmetics inakutakia wewe na familia yako Sikukuu njema ya Eid al-Fitr.',
              en: 'Eid Mubarak {name}! Double H Cosmetics wishes you and your family a blessed Eid al-Fitr.' },
            { sw_name: 'Eid al-Adha', en_name: 'Eid al-Adha',
              sw: 'Eid Mubarak {name}! Double H Cosmetics inakutakia Sikukuu njema ya Eid al-Adha yenye baraka tele.',
              en: 'Eid Mubarak {name}! Double H Cosmetics wishes you a blessed Eid al-Adha.' },
            { sw_name: 'Pasaka', en_name: 'Easter',
              sw: 'Heri ya Pasaka {name}! Double H Cosmetics inakutakia sikukuu njema ya Pasaka yenye amani na baraka.',
              en: 'Happy Easter {name}! Double H Cosmetics wishes you a peaceful and blessed Easter.' },
            { sw_name: 'Maulid', en_name: 'Maulid',
              sw: 'Heri ya Maulid {name}! Double H Cosmetics inakutakia sikukuu njema yenye baraka.',
              en: 'Happy Maulid {name}! Best wishes from Double H Cosmetics.' },
        ],
    };
    const months = { sw: ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ago', 'Sep', 'Okt', 'Nov', 'Des'], en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] };
    const lang = () => form.querySelector('[name=tpl_lang]:checked').value;

    function useTemplate(text) {
        message.value = text;
        message.focus();
        update();
    }

    // Days until a fixed-date holiday (0 = today), rolling over to next year.
    function daysUntil(h) {
        const today = new Date(); today.setHours(0, 0, 0, 0);
        let next = new Date(today.getFullYear(), h.m - 1, h.d);
        if (next < today) next = new Date(today.getFullYear() + 1, h.m - 1, h.d);
        return Math.round((next - today) / 86400000);
    }

    function renderTemplates() {
        const l = lang();
        const box = document.getElementById('generalTemplates');
        box.replaceChildren();
        templates.general.forEach(function (t) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'btn btn-sm btn-light border sms-template';
            b.textContent = t.label[l];
            b.addEventListener('click', () => useTemplate(t[l]));
            box.appendChild(b);
        });

        const select = document.getElementById('holidayTemplate');
        select.replaceChildren(new Option(l === 'sw' ? '🎉 Salamu za sikukuu…' : '🎉 Holiday wishes…', ''));
        const fixed = templates.holidays.filter(h => h.m).sort((a, b) => daysUntil(a) - daysUntil(b));
        const movable = templates.holidays.filter(h => !h.m);
        const g1 = document.createElement('optgroup');
        g1.label = l === 'sw' ? 'Zijazo (kwa tarehe)' : 'Coming up (by date)';
        fixed.forEach(function (h) {
            const days = daysUntil(h);
            const when = days === 0 ? (l === 'sw' ? 'leo' : 'today') : h.d + ' ' + months[l][h.m - 1];
            g1.appendChild(new Option(h[l + '_name'] + ' — ' + when, templates.holidays.indexOf(h)));
        });
        const g2 = document.createElement('optgroup');
        g2.label = l === 'sw' ? 'Tarehe hubadilika' : 'Date changes each year';
        movable.forEach(h => g2.appendChild(new Option(h[l + '_name'], templates.holidays.indexOf(h))));
        select.append(g1, g2);

        const next = fixed[0];
        const days = daysUntil(next);
        document.getElementById('nextHoliday').textContent = (l === 'sw' ? 'Inayofuata: ' : 'Next: ') + next[l + '_name']
            + (days === 0 ? (l === 'sw' ? ' (leo)' : ' (today)') : ' (' + (l === 'sw' ? 'siku ' + days : days + ' days') + ')');
    }

    document.getElementById('holidayTemplate').addEventListener('change', function () {
        if (this.value === '') return;
        useTemplate(templates.holidays[this.value][lang()]);
    });
    form.querySelectorAll('[name=tpl_lang]').forEach(r => r.addEventListener('change', renderTemplates));
    renderTemplates();

    // Confirm before sending real (paid) messages.
    document.getElementById('smsReview').addEventListener('click', function () {
        if (!form.reportValidity()) return;
        const rec = recipients();
        if (!rec.length) { Swal.fire('No recipients', 'Choose at least one person or type a number.', 'warning'); return; }
        const parts = Math.ceil(message.value.length / 160);
        Swal.fire({
            icon: 'question',
            title: 'Send ' + rec.length + ' SMS?',
            html: '<div class="text-start small">'
                + '<div class="d-flex justify-content-between"><span>Recipients</span><strong>' + rec.length + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>SMS per person</span><strong>' + parts + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>Total SMS (credits)</span><strong>' + (parts * rec.length) + '</strong></div>'
                + '</div>',
            showCancelButton: true,
            confirmButtonText: 'Yes, send',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#831619',
            reverseButtons: true,
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            const btn = document.getElementById('smsReview');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
            form.submit();
        });
    });

    renderList();
    update();
});
</script>
@endif
@endsection
