{{--
    "Financial year" dropdown for report filters: picking a year fills the
    form's From / To dates with that year and applies the filter.
    Needs $from and $to (Carbon) from the page.
--}}
@php
    $fyYears = \App\Models\FinancialYear::forFilters();
    $fyFrom = isset($from) ? $from->toDateString() : null;
    $fyTo = isset($to) ? $to->toDateString() : null;
@endphp
@if($fyYears->isNotEmpty())
    <div class="col-sm-6 col-md-2">
        <label class="form-label small mb-1">Financial year</label>
        <select class="form-select form-select-sm fy-picker" aria-label="Financial year">
            <option value="">Custom dates</option>
            @foreach($fyYears as $fy)
                @php $fyEndShown = $fy->end_date->isFuture() ? now()->toDateString() : $fy->end_date->toDateString(); @endphp
                <option value="{{ $fy->id }}" data-from="{{ $fy->start_date->toDateString() }}" data-to="{{ $fyEndShown }}"
                    {{ $fyFrom === $fy->start_date->toDateString() && in_array($fyTo, [$fyEndShown, $fy->end_date->toDateString()], true) ? 'selected' : '' }}>
                    {{ $fy->name }}{{ $fy->is_current ? ' (current)' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    @once
        <script>
        document.addEventListener('change', function (e) {
            const picker = e.target.closest('.fy-picker');
            if (!picker) return;
            const option = picker.selectedOptions[0];
            if (!option || !option.dataset.from) return;
            const form = picker.closest('form');
            form.querySelector('[name=from]').value = option.dataset.from;
            form.querySelector('[name=to]').value = option.dataset.to;
            form.requestSubmit ? form.requestSubmit() : form.submit();
        });
        // Typing your own dates switches the picker back to "Custom dates".
        document.addEventListener('input', function (e) {
            if (!e.target.matches('input[name=from], input[name=to]')) return;
            const picker = e.target.closest('form')?.querySelector('.fy-picker');
            if (picker) picker.value = '';
        });
        </script>
    @endonce
@endif
