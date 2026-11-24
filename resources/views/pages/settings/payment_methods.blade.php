@extends('main')

@section('title', 'Settings')

@section('content')
@php
    $categoryBadge = ['cash' => 'bg-success', 'mobile' => 'bg-warning text-dark', 'bank' => 'bg-primary', 'credit' => 'bg-danger'];
    $categoryIcon = ['cash' => 'bi-cash-stack', 'mobile' => 'bi-phone', 'bank' => 'bi-bank', 'credit' => 'bi-journal-text'];
@endphp
<div class="container-fluid py-4">
    @include('pages.settings._tabs')

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Payment Methods</h5>
                <p class="settings-help">Active methods are offered on the POS and the mobile app, in this order.</p>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#methodModal" data-mode="create">
                <i class="bi bi-plus-lg"></i> New Payment Method
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:70px">Order</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Account</th>
                            <th class="text-center">Sales</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width:150px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($methods as $method)
                            <tr class="{{ $method->is_active ? '' : 'text-muted' }}">
                                <td class="text-center">{{ $method->sort_order }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $method->name }}</div>
                                    @if($method->notes)<div class="small text-muted">{{ $method->notes }}</div>@endif
                                </td>
                                <td>
                                    <span class="badge {{ $categoryBadge[$method->category] ?? 'bg-secondary' }}">
                                        <i class="bi {{ $categoryIcon[$method->category] ?? 'bi-wallet' }} me-1"></i>{{ $method->categoryLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if($method->account_name)<div>{{ $method->account_name }}</div>@endif
                                    @if($method->account_number)<div class="font-monospace small">{{ $method->account_number }}</div>@endif
                                    @if(!$method->account_name && !$method->account_number)<span class="text-muted">—</span>@endif
                                </td>
                                <td class="text-center">{{ number_format($method->sales_count) }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('settings.payment-methods.toggle', $method) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $method->is_active ? 'btn-success' : 'btn-outline-secondary' }}"
                                                title="{{ $method->is_active ? 'Click to turn off' : 'Click to turn on' }}">
                                            <i class="bi {{ $method->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                            {{ $method->is_active ? 'Active' : 'Off' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#methodModal"
                                            data-mode="edit" data-action="{{ route('settings.payment-methods.update', $method) }}"
                                            data-name="{{ $method->name }}" data-category="{{ $method->category }}"
                                            data-account-name="{{ $method->account_name }}" data-account-number="{{ $method->account_number }}"
                                            data-notes="{{ $method->notes }}" data-sort-order="{{ $method->sort_order }}"
                                            data-active="{{ $method->is_active ? 1 : 0 }}"
                                            aria-label="Edit {{ $method->name }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('settings.payment-methods.destroy', $method) }}" class="d-inline"
                                          onsubmit="return confirm('Delete payment method {{ addslashes($method->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" {{ $method->sales_count ? 'disabled' : '' }}
                                                title="{{ $method->sales_count ? 'Used on sales — turn it off instead' : 'Delete' }}"
                                                aria-label="Delete {{ $method->name }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No payment methods yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="alert alert-light border small mt-3 mb-0">
                <strong>How the type works:</strong>
                <b>Cash</b> — the amount received must cover the total and change is worked out.
                <b>Mobile money</b> / <b>Bank</b> — paid in full.
                <b>Credit</b> — needs a customer; any unpaid part becomes the customer's debt.
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="methodModal" tabindex="-1" aria-labelledby="methodModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('settings.payment-methods.store') }}" data-store="{{ route('settings.payment-methods.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="_edit_action" value="{{ old('_edit_action') }}">

            <div class="modal-header">
                <h5 class="modal-title" id="methodModalTitle">New Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="pmName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="pmName" class="form-control" maxlength="100" value="{{ old('name') }}"
                               placeholder="e.g. CRDB, Lipa na M-Pesa" required>
                    </div>
                    <div class="col-md-5">
                        <label for="pmCategory" class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="category" id="pmCategory" class="form-select" required>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="pmAccountName" class="form-label">Account Name</label>
                        <input type="text" name="account_name" id="pmAccountName" class="form-control" maxlength="150" value="{{ old('account_name') }}"
                               placeholder="e.g. Double H Cosmetics Ltd">
                    </div>
                    <div class="col-md-6">
                        <label for="pmAccountNumber" class="form-label">Account / Till Number</label>
                        <input type="text" name="account_number" id="pmAccountNumber" class="form-control" maxlength="100" value="{{ old('account_number') }}"
                               placeholder="e.g. 0150123456789 or Till 123456">
                    </div>
                    <div class="col-12">
                        <label for="pmNotes" class="form-label">Notes</label>
                        <input type="text" name="notes" id="pmNotes" class="form-control" maxlength="255" value="{{ old('notes') }}"
                               placeholder="Optional, e.g. branch or who receives it">
                    </div>
                    <div class="col-md-5">
                        <label for="pmSort" class="form-label">Display Order</label>
                        <input type="number" name="sort_order" id="pmSort" class="form-control" min="0" step="1" value="{{ old('sort_order') }}"
                               placeholder="Last">
                    </div>
                    <div class="col-md-7 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="pmActive"
                                   {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="pmActive">Active (offered on the POS)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i><span id="pmSubmit">Add</span></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('methodModal');
    const form = modalEl.querySelector('form');
    const field = id => document.getElementById(id);

    function setMode(editing, action) {
        form.action = editing ? action : form.dataset.store;
        form.querySelector('[name=_method]').value = editing ? 'PUT' : 'POST';
        form.querySelector('[name=_edit_action]').value = editing ? action : '';
        field('methodModalTitle').textContent = editing ? 'Edit Payment Method' : 'New Payment Method';
        field('pmSubmit').textContent = editing ? 'Save changes' : 'Add';
    }

    modalEl.addEventListener('show.bs.modal', function (event) {
        const t = event.relatedTarget;
        if (!t) return; // reopened after a validation error: keep what was typed
        const editing = t.dataset.mode === 'edit';
        setMode(editing, t.dataset.action);
        field('pmName').value = editing ? t.dataset.name : '';
        field('pmCategory').value = editing ? t.dataset.category : 'mobile';
        field('pmAccountName').value = editing ? (t.dataset.accountName || '') : '';
        field('pmAccountNumber').value = editing ? (t.dataset.accountNumber || '') : '';
        field('pmNotes').value = editing ? (t.dataset.notes || '') : '';
        field('pmSort').value = editing ? t.dataset.sortOrder : '';
        field('pmActive').checked = editing ? t.dataset.active === '1' : true;
    });
    modalEl.addEventListener('shown.bs.modal', () => field('pmName').focus());

    @if($errors->any())
        const editAction = @json(old('_edit_action'));
        setMode(!!editAction, editAction);
        new bootstrap.Modal(modalEl).show();
    @endif
});
</script>
@endsection
