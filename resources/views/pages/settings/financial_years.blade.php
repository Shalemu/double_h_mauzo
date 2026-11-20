@extends('main')

@section('title', 'Settings')

@section('content')
<div class="container-fluid py-4">
    @include('pages.settings._tabs')

    @if(!$current)
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i> No financial year is marked as current. Make one current below.</div>
    @elseif(!$current->contains(now()))
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Today ({{ now()->format('j M Y') }}) is outside the current financial year <strong>{{ $current->name }}</strong> ({{ $current->period() }}).
            Create the next financial year and make it current.
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Financial Years</h5>
                <p class="settings-help">The current year is the one the business is operating in. It is shown in the sidebar and can be picked on every report.</p>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#yearModal" data-mode="create">
                <i class="bi bi-plus-lg"></i> New Financial Year
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width:210px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($years as $year)
                            <tr class="{{ $year->is_current ? 'table-success' : '' }}">
                                <td class="fw-semibold">{{ $year->name }}</td>
                                <td>{{ $year->start_date->format('j M Y') }}</td>
                                <td>{{ $year->end_date->format('j M Y') }}</td>
                                <td class="text-center">
                                    @if($year->is_current)
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Current</span>
                                    @elseif($year->end_date->lt(now()->startOfDay()))
                                        <span class="badge bg-secondary">Past</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Upcoming</span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    @unless($year->is_current)
                                        <form method="POST" action="{{ route('settings.financial-years.current', $year) }}" class="d-inline"
                                              onsubmit="return confirm('Make {{ addslashes($year->name) }} the current financial year?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-check2-circle"></i> Make current</button>
                                        </form>
                                    @endunless
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#yearModal"
                                            data-mode="edit" data-action="{{ route('settings.financial-years.update', $year) }}"
                                            data-name="{{ $year->name }}" data-start="{{ $year->start_date->toDateString() }}"
                                            data-end="{{ $year->end_date->toDateString() }}" data-current="{{ $year->is_current ? 1 : 0 }}"
                                            aria-label="Edit {{ $year->name }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('settings.financial-years.destroy', $year) }}" class="d-inline"
                                          onsubmit="return confirm('Delete financial year {{ addslashes($year->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" {{ $year->is_current ? 'disabled' : '' }}
                                                title="{{ $year->is_current ? 'Make another year current first' : 'Delete' }}"
                                                aria-label="Delete {{ $year->name }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No financial years yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="yearModal" tabindex="-1" aria-labelledby="yearModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('settings.financial-years.store') }}" data-store="{{ route('settings.financial-years.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="_edit_action" value="{{ old('_edit_action') }}">

            <div class="modal-header">
                <h5 class="modal-title" id="yearModalTitle">New Financial Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="fyName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="fyName" class="form-control" maxlength="50" value="{{ old('name') }}"
                               placeholder="e.g. FY 2027 or 2026/2027" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="fyStart" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="fyStart" class="form-control" value="{{ old('start_date') }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="fyEnd" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="fyEnd" class="form-control" value="{{ old('end_date') }}" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_current" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_current" value="1" id="fyCurrent"
                                   {{ old('is_current') ? 'checked' : '' }}>
                            <label class="form-check-label" for="fyCurrent">Make this the current financial year</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i><span id="fySubmit">Create</span></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('yearModal');
    const form = modalEl.querySelector('form');
    const field = id => document.getElementById(id);
    const suggested = @json(['start' => $suggested['start']->toDateString(), 'end' => $suggested['end']->toDateString()]);

    function setMode(editing, action) {
        form.action = editing ? action : form.dataset.store;
        form.querySelector('[name=_method]').value = editing ? 'PUT' : 'POST';
        form.querySelector('[name=_edit_action]').value = editing ? action : '';
        field('yearModalTitle').textContent = editing ? 'Edit Financial Year' : 'New Financial Year';
        field('fySubmit').textContent = editing ? 'Save changes' : 'Create';
    }

    modalEl.addEventListener('show.bs.modal', function (event) {
        const t = event.relatedTarget;
        if (!t) return; // reopened after a validation error: keep what was typed
        const editing = t.dataset.mode === 'edit';
        setMode(editing, t.dataset.action);
        field('fyStart').value = editing ? t.dataset.start : suggested.start;
        field('fyEnd').value = editing ? t.dataset.end : suggested.end;
        field('fyName').value = editing ? t.dataset.name : suggestName(suggested.start, suggested.end);
        field('fyCurrent').checked = editing && t.dataset.current === '1';
        // The current year stays current; make another one current to change it.
        field('fyCurrent').disabled = editing && t.dataset.current === '1';
    });
    modalEl.addEventListener('shown.bs.modal', () => field('fyName').focus());

    // "FY 2027" for a calendar year, "FY 2026/2027" when it spans two years.
    function suggestName(start, end) {
        const a = start.slice(0, 4), b = end.slice(0, 4);
        return a === b ? 'FY ' + a : 'FY ' + a + '/' + b;
    }

    @if($errors->any())
        const editAction = @json(old('_edit_action'));
        setMode(!!editAction, editAction);
        new bootstrap.Modal(modalEl).show();
    @endif
});
</script>
@endsection
