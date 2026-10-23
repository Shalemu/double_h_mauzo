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

    <?php if($errors->any()): ?>
        // Reopen with what was typed; if it was an edit, keep saving to that unit.
        const editAction = <?php echo json_encode(old('_edit_action'), 15, 512) ?>;
        if (editAction) {
            form.action = editAction;
            form.querySelector('[name=_method]').value = 'PUT';
            form.querySelector('[name=_edit_action]').value = editAction;
            setMode(true);
        }
        syncMultiple();
        new bootstrap.Modal(modalEl).show();
    <?php endif; ?>
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/units/_index_scripts.blade.php ENDPATH**/ ?>