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
@include('pages.sms._index_styles')

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
@include('pages.sms._index_scripts')
@endif
@endsection
