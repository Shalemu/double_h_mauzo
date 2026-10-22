<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('.nav-item a');
        const sections = document.querySelectorAll('.dashboard-section');
        const storageKey = 'dashboard-active-section';

        function activateSection(target) {
            if (!target || !document.getElementById(target)) return false;

            menuItems.forEach(i => i.parentElement.classList.remove('active'));
            sections.forEach(sec => sec.style.display = 'none');

            document.getElementById(target).style.display = 'block';

            const matchingItem = Array.from(menuItems).find(i => i.getAttribute('data-content') === target);
            if (matchingItem) matchingItem.parentElement.classList.add('active');

            return true;
        }

        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                const target = item.getAttribute('data-content');
                if (activateSection(target)) {
                    localStorage.setItem(storageKey, target);
                }
            });
        });

        // Restore whichever section was open before the page was refreshed
        const savedTarget = localStorage.getItem(storageKey);
        if (savedTarget) activateSection(savedTarget);
    });
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\_dashboard_scripts.blade.php ENDPATH**/ ?>