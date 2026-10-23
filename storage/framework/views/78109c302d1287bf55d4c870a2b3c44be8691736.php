<script>
document.getElementById('brandModal').addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const form = this.querySelector('form');
    const editing = trigger && trigger.dataset.mode === 'edit';
    form.action = editing ? trigger.dataset.action : form.dataset.store;
    form.querySelector('[name=_method]').value = editing ? 'PUT' : 'POST';
    this.querySelector('.modal-title').textContent = editing ? 'Edit Brand' : 'New Brand';
    form.querySelector('[name=name]').value = editing ? trigger.dataset.name : '';
    form.querySelector('[name=description]').value = editing ? (trigger.dataset.description || '') : '';
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\brands\_index_scripts.blade.php ENDPATH**/ ?>