<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rpPermissionsPanel');
    if (!form) return;

    const locked = <?php echo json_encode($locked, 15, 512) ?>;
    const total = <?php echo e($totalPermissions); ?>;
    const boxes = Array.from(form.querySelectorAll('.rp-permission-checkbox'));
    const saved = new Set(boxes.filter(b => b.checked).map(b => b.value));
    const saveBar = document.getElementById('rpSaveBar');
    const sidebarCount = document.getElementById('rpSidebarCount');

    function changes() {
        return boxes.filter(b => b.checked !== saved.has(b.value)).length;
    }

    function refresh() {
        const granted = boxes.filter(b => b.checked).length;
        document.getElementById('rpGranted').textContent = granted;
        document.getElementById('rpProgressBar').style.width = (total ? Math.round(granted / total * 100) : 0) + '%';
        if (sidebarCount && !locked) sidebarCount.textContent = granted;

        form.querySelectorAll('.rp-module').forEach(function (module) {
            const own = boxes.filter(b => b.dataset.module === module.dataset.module);
            const on = own.filter(b => b.checked).length;
            module.querySelector('.rp-module-count').textContent = on;
            module.querySelector('.rp-module-card').classList.toggle('has-checked', on > 0);
            module.querySelector('.rp-module-toggle').checked = on === own.length;
        });

        const changed = changes();
        saveBar.classList.toggle('rp-hidden', locked || changed === 0);
        document.getElementById('rpSaveBarText').textContent = changed + ' unsaved change' + (changed === 1 ? '' : 's');
    }

    boxes.forEach(b => b.addEventListener('change', refresh));

    form.querySelectorAll('.rp-module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            boxes.filter(b => b.dataset.module === toggle.dataset.module).forEach(b => { b.checked = toggle.checked; });
            refresh();
        });
    });

    const selectAll = document.getElementById('rpSelectAllBtn');
    const clearAll = document.getElementById('rpClearAllBtn');
    if (selectAll) selectAll.addEventListener('click', () => { boxes.forEach(b => { b.checked = true; }); refresh(); });
    if (clearAll) clearAll.addEventListener('click', () => { boxes.forEach(b => { b.checked = false; }); refresh(); });

    document.getElementById('rpResetBtn').addEventListener('click', function () {
        boxes.forEach(b => { b.checked = saved.has(b.value); });
        refresh();
    });

    // Search: a module matching by name shows all its permissions;
    // otherwise only matching permissions; empty modules/sections hide.
    document.getElementById('rpFilterInput').addEventListener('input', function () {
        const text = this.value.trim().toLowerCase();
        let anyVisible = false;
        form.querySelectorAll('.rp-section').forEach(function (section) {
            let sectionVisible = false;
            section.querySelectorAll('.rp-module').forEach(function (module) {
                const moduleMatch = !text || module.dataset.label.includes(text);
                let moduleVisible = false;
                module.querySelectorAll('.rp-permission').forEach(function (item) {
                    const show = moduleMatch || item.dataset.label.includes(text);
                    item.classList.toggle('rp-hidden', !show);
                    moduleVisible = moduleVisible || show;
                });
                module.classList.toggle('rp-hidden', !moduleVisible);
                sectionVisible = sectionVisible || moduleVisible;
            });
            section.classList.toggle('rp-hidden', !sectionVisible);
            anyVisible = anyVisible || sectionVisible;
        });
        document.getElementById('rpNoMatch').classList.toggle('rp-hidden', anyVisible);
    });

    // Permissions / Users tabs
    document.querySelectorAll('.rp-tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.rp-tab-btn').forEach(b => b.classList.toggle('active', b === btn));
            document.querySelectorAll('[data-tab-panel]').forEach(p => p.classList.toggle('rp-hidden', p.dataset.tabPanel !== btn.dataset.tab));
            saveBar.classList.toggle('rp-hidden', btn.dataset.tab !== 'permissions' || locked || changes() === 0);
        });
    });

    // Don't lose ticks by switching role mid-edit.
    document.querySelectorAll('.rp-role-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (changes() > 0 && !confirm('Discard unsaved changes? Switching roles now will lose what you just changed.')) {
                event.preventDefault();
            }
        });
    });
    form.addEventListener('submit', function () {
        if (locked) return false;
        document.getElementById('rpSaveBtn').textContent = 'Saving...';
    });

    refresh();
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/role/_index_scripts.blade.php ENDPATH**/ ?>