@extends('main')

@section('title', 'Unit of Measure')

@section('content')
@include('pages.units._index_styles')

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

@include('pages.units._index_scripts')
@endsection
