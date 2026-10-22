<script>
document.getElementById('categoryModal').addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const form = this.querySelector('form');
    const editing = trigger && trigger.dataset.mode === 'edit';
    const parent = form.querySelector('[name=parent_id]');
    form.action = editing ? trigger.dataset.action : form.dataset.store;
    form.querySelector('[name=_method]').value = editing ? 'PUT' : 'POST';
    this.querySelector('.modal-title').textContent = editing ? 'Edit Category' : 'New Category';
    form.querySelector('[name=name]').value = editing ? trigger.dataset.name : '';
    form.querySelector('[name=description]').value = editing ? (trigger.dataset.description || '') : '';
    parent.value = editing ? (trigger.dataset.parent || '') : '';
    // A category can't be its own parent.
    Array.from(parent.options).forEach(o => { o.disabled = editing && o.value === trigger.dataset.id; });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\categories\_index_scripts.blade.php ENDPATH**/ ?>