@extends('main')

@section('title', 'Unit of Measure')

@section('content')
<style>
/* Register / Edit Unit modal — compact */
.unit-dialog { max-width:520px; }
.unit-modal { border:0; border-radius:.9rem; overflow:hidden; box-shadow:0 18px 48px rgba(15,23,42,.22); }
.unit-modal-header { gap:.65rem; padding:.9rem 1.25rem; border-bottom:1px solid #f0f1f3; }
.unit-modal-header .modal-title { font-size:1.05rem; font-weight:600; flex-grow:1; }
.unit-modal-icon {
    width:34px; height:34px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:rgba(131,22,25,.1); color:#831619;
}
.unit-modal .modal-body { padding:1.1rem 1.25rem; }
.unit-modal .form-label { font-size:.82rem; font-weight:500; color:#4b5563; margin-bottom:.3rem; }
.unit-modal .modal-body > .mb-3 .form-control, .unit-modal .row .form-control, .unit-modal .unit-segment { height:38px; }
.unit-modal .form-control, .unit-modal .form-select { border-radius:.5rem; }
.unit-modal .form-control::placeholder { color:#b8bec7; }
.unit-modal .form-control:focus, .unit-modal .form-select:focus { border-color:#c97a7d; box-shadow:0 0 0 .2rem rgba(131,22,25,.12); }
.unit-segment { display:flex; border:1px solid #dee2e6; border-radius:.5rem; padding:2px; background:#f8f9fa; }
.unit-segment label {
    flex:1; display:flex; align-items:center; justify-content:center; border-radius:.4rem;
    font-size:.88rem; color:#6b7280; cursor:pointer; transition:.15s; margin:0;
}
.unit-segment .btn-check:checked + label { background:#fff; color:#831619; font-weight:600; box-shadow:0 1px 3px rgba(15,23,42,.12); }
.unit-segment .btn-check:focus-visible + label { outline:2px solid #831619; outline-offset:1px; }
.unit-multiple { border-top:1px dashed #e5e7eb; padding-top:.9rem; }
.unit-switch .form-check-label { font-size:.9rem; font-weight:500; color:#374151; }
.unit-switch .form-check-input { cursor:pointer; }
.unit-switch .form-check-input:checked { background-color:#831619; border-color:#831619; }
.unit-switch .form-check-input:focus { box-shadow:0 0 0 .2rem rgba(131,22,25,.15); }
.unit-equation {
    display:flex; align-items:center; gap:.5rem; margin-top:.75rem;
    padding:.55rem .65rem; border-radius:.6rem; background:#fbf6f6; border:1px solid #f0dcdd;
}
.unit-eq-one {
    width:30px; height:30px; border-radius:8px; background:#831619; color:#fff; flex-shrink:0;
    display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem;
}
.unit-eq-name { font-weight:600; color:#1f2937; max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.unit-eq-sign { font-weight:700; color:#831619; }
.unit-eq-multiplier { width:70px; flex-shrink:0; text-align:center; font-weight:600; }
.unit-eq-base { flex:1; min-width:0; }
.unit-preview { margin-top:.5rem; font-size:.8rem; color:#15803d; }
.unit-note { margin-top:.5rem; font-size:.8rem; color:#92400e; }
.unit-modal .modal-footer { padding:.7rem 1.25rem; border-top:1px solid #f0f1f3; }

/* Units list */
.unit-conversion {
    display:inline-flex; align-items:center; gap:.3rem; margin-top:.2rem;
    font-size:.75rem; color:#831619; background:rgba(131,22,25,.07); border-radius:999px; padding:.1rem .55rem;
}
.unit-base-tag { font-size:.72rem; color:#6b7280; background:#f3f4f6; border-radius:999px; padding:.1rem .55rem; }
</style>

<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-rulers me-2"></i>Unit of Measure</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Products</a>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#unitModal" data-mode="create">
                    <i class="bi bi-plus-lg"></i> New Unit
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 mb-3" style="max-width:420px">
                <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search units..." aria-label="Search units">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th class="text-start">Name</th><th>Short name</th><th>Allow decimal</th><th>Products</th><th style="width:120px">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr>
                                <td class="text-center">{{ $units->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $unit->name }}</div>
                                    @if($unit->conversion)
                                        <span class="unit-conversion"><i class="bi bi-arrow-repeat"></i> {{ $unit->conversion }}</span>
                                    @elseif($unit->multiples_count)
                                        <span class="unit-base-tag"><i class="bi bi-diagram-3"></i> Base unit</span>
                                    @endif
                                </td>
                                <td class="text-center"><span class="badge bg-secondary font-monospace">{{ $unit->short_name }}</span></td>
                                <td class="text-center">{{ $unit->allow_decimal ? 'Yes' : 'No' }}</td>
                                <td class="text-center">{{ number_format($unit->products_count) }}</td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#unitModal"
                                            data-mode="edit" data-action="{{ route('units.update', $unit->id) }}"
                                            data-id="{{ $unit->id }}"
                                            data-name="{{ $unit->name }}" data-short-name="{{ $unit->short_name }}"
                                            data-allow-decimal="{{ $unit->allow_decimal ? 1 : 0 }}"
                                            data-base-unit="{{ $unit->base_unit_id }}" data-multiplier="{{ $unit->multiplier }}"
                                            data-is-base="{{ $unit->multiples_count ? 1 : 0 }}"
                                            aria-label="Edit {{ $unit->name }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('units.destroy', $unit->id) }}" class="d-inline"
                                          onsubmit="return confirm('Delete unit {{ addslashes($unit->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        @php $locked = $unit->products_count > 0 || $unit->multiples_count > 0; @endphp
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $unit->name }}"
                                                {{ $locked ? 'disabled' : '' }}
                                                title="{{ $unit->products_count ? 'In use by products' : ($unit->multiples_count ? 'Other units are multiples of it' : 'Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No units found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">@if($units->total()) Showing {{ $units->firstItem() }}–{{ $units->lastItem() }} of {{ number_format($units->total()) }} @endif</small>
                {{ $units->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered unit-dialog">
        <form class="modal-content unit-modal" method="POST" action="{{ route('units.store') }}" data-store="{{ route('units.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            {{-- Remembers which unit was being edited if validation sends us back. --}}
            <input type="hidden" name="_edit_action" value="">

            <div class="modal-header unit-modal-header">
                <div class="unit-modal-icon"><i class="bi bi-rulers"></i></div>
                <h5 class="modal-title mb-0" id="unitModalTitle">Register Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label d-block" for="unitName">Unit Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="unitName" class="form-control" maxlength="100" value="{{ old('name') }}" placeholder="e.g. Pieces, Dozen, Carton, Kilograms" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label d-block" for="unitShort">Short Name <span class="text-danger">*</span></label>
                        <input type="text" name="short_name" id="unitShort" class="form-control" maxlength="10" value="{{ old('short_name') }}" placeholder="e.g. pcs, dzn, ctn, kg" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label d-block" id="decimalLabel" for="decimalNo">Allow Decimal? <span class="text-danger">*</span></label>
                        <div class="unit-segment" role="radiogroup" aria-labelledby="decimalLabel">
                            <input type="radio" class="btn-check" name="allow_decimal" id="decimalNo" value="0" {{ old('allow_decimal', '0') == '0' ? 'checked' : '' }}>
                            <label for="decimalNo">No</label>
                            <input type="radio" class="btn-check" name="allow_decimal" id="decimalYes" value="1" {{ old('allow_decimal') == '1' ? 'checked' : '' }}>
                            <label for="decimalYes">Yes</label>
                        </div>
                    </div>
                </div>

                <div class="unit-multiple">
                    <div class="form-check form-switch m-0 unit-switch">
                        <input type="hidden" name="is_multiple" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_multiple" value="1" id="isMultiple" {{ old('is_multiple') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isMultiple">Register as a multiple of other unit?</label>
                    </div>
                    <div class="unit-note" id="isBaseNote" hidden>
                        <i class="bi bi-info-circle"></i> Other units are multiples of this one, so it must stay a base unit.
                    </div>

                    <div class="unit-equation" id="multipleRow" hidden>
                        <span class="unit-eq-one">1</span>
                        <span class="unit-eq-name" id="unitNameCopyLabel">Unit</span>
                        <input type="hidden" id="unitNameCopy" value="Unit">
                        <span class="unit-eq-sign">=</span>
                        <input type="number" name="multiplier" id="multiplier" class="form-control form-control-sm unit-eq-multiplier" min="2" step="1" value="{{ old('multiplier') }}" placeholder="24" aria-label="How many base units">
                        <select name="base_unit_id" id="baseUnit" class="form-select form-select-sm unit-eq-base" aria-label="Base unit">
                            <option value="">-- select unit --</option>
                            @foreach($baseUnits as $base)
                                <option value="{{ $base->id }}" {{ old('base_unit_id') == $base->id ? 'selected' : '' }}>{{ $base->name }} ({{ $base->short_name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="unit-preview" id="conversionPreviewWrap" hidden>
                        <i class="bi bi-check2-circle"></i> <span id="conversionPreview"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary px-4" id="unitSubmit"><i class="bi bi-check-lg me-1"></i><span>Register</span></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('unitModal');
    const form = modalEl.querySelector('form');
    const name = document.getElementById('unitName');
    const nameCopy = document.getElementById('unitNameCopy');
    const isMultiple = document.getElementById('isMultiple');
    const row = document.getElementById('multipleRow');
    const multiplier = document.getElementById('multiplier');
    const baseUnit = document.getElementById('baseUnit');
    const preview = document.getElementById('conversionPreview');
    const isBaseNote = document.getElementById('isBaseNote');

    const title = document.getElementById('unitModalTitle');
    const submitLabel = document.querySelector('#unitSubmit span');

    function setMode(editing) {
        title.textContent = editing ? 'Edit Unit' : 'Register Unit';
        submitLabel.textContent = editing ? 'Save changes' : 'Register';
    }

    function syncMultiple() {
        row.hidden = !isMultiple.checked;
        multiplier.required = baseUnit.required = isMultiple.checked;
        nameCopy.value = name.value.trim() || 'Unit';
        document.getElementById('unitNameCopyLabel').textContent = nameCopy.value;
        const base = baseUnit.options[baseUnit.selectedIndex];
        const ready = isMultiple.checked && multiplier.value && baseUnit.value;
        preview.textContent = ready ? '1 ' + nameCopy.value + ' holds ' + multiplier.value + ' ' + base.text + '.' : '';
        document.getElementById('conversionPreviewWrap').hidden = !ready;
    }

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return; // reopened after a validation error — keep old input
        const editing = trigger.dataset.mode === 'edit';
        form.action = editing ? trigger.dataset.action : form.dataset.store;
        form.querySelector('[name=_method]').value = editing ? 'PUT' : 'POST';
        form.querySelector('[name=_edit_action]').value = editing ? trigger.dataset.action : '';
        setMode(editing);

        name.value = editing ? trigger.dataset.name : '';
        document.getElementById('unitShort').value = editing ? trigger.dataset.shortName : '';
        document.getElementById(editing && trigger.dataset.allowDecimal === '1' ? 'decimalYes' : 'decimalNo').checked = true;
        isMultiple.checked = editing && !!trigger.dataset.baseUnit;
        multiplier.value = editing ? (trigger.dataset.multiplier || '') : '';
        baseUnit.value = editing ? (trigger.dataset.baseUnit || '') : '';

        // A unit can't be a multiple of itself, and a base unit others use stays a base.
        Array.from(baseUnit.options).forEach(o => { o.hidden = editing && o.value === trigger.dataset.id; });
        const isBase = editing && trigger.dataset.isBase === '1';
        isMultiple.disabled = isBase;
        isBaseNote.hidden = !isBase;
        syncMultiple();
    });

    [isMultiple, multiplier, baseUnit].forEach(el => el.addEventListener('input', syncMultiple));
    isMultiple.addEventListener('change', syncMultiple);
    name.addEventListener('input', syncMultiple);

    @if($errors->any())
        // Reopen with what was typed; if it was an edit, keep saving to that unit.
        const editAction = @json(old('_edit_action'));
        if (editAction) {
            form.action = editAction;
            form.querySelector('[name=_method]').value = 'PUT';
            form.querySelector('[name=_edit_action]').value = editAction;
            setMode(true);
        }
        syncMultiple();
        new bootstrap.Modal(modalEl).show();
    @endif
});
</script>
@endsection
