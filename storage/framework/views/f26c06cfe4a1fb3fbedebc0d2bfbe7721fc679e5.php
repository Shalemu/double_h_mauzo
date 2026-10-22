<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('settleForm');
    const modalEl = document.getElementById('settleConfirmModal');
    const modal = new bootstrap.Modal(modalEl);
    const fmt = n => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('settleReview').addEventListener('click', function () {
        // Let the browser flag empty/invalid fields first.
        if (!form.reportValidity()) return;

        let returned = 0, sold = 0, total = 0;
        const lines = document.getElementById('cfLines');
        lines.replaceChildren();

        form.querySelectorAll('tbody tr[data-taken]').forEach(function (row) {
            const taken = parseInt(row.dataset.taken, 10);
            const back = Math.min(Math.max(parseInt(row.querySelector('.js-returned').value, 10) || 0, 0), taken);
            const price = parseFloat(row.querySelector('.js-price').value) || 0;
            const out = taken - back;
            returned += back; sold += out; total += out * price;

            const tr = document.createElement('tr');
            [[row.cells[0].textContent.trim(), ''], [taken, 'text-center'], [back, 'text-center text-success'],
             [out, 'text-center fw-semibold'], [fmt(out * price), 'text-end']].forEach(function ([text, cls]) {
                const td = document.createElement('td');
                td.textContent = text;
                td.className = cls;
                tr.appendChild(td);
            });
            lines.appendChild(tr);
        });

        const received = parseFloat(document.getElementById('amountReceived').value) || 0;
        const balance = Math.max(total - received, 0);
        document.getElementById('cfReturned').textContent = returned;
        document.getElementById('cfSold').textContent = sold;
        document.getElementById('cfTotal').textContent = fmt(total);
        document.getElementById('cfReceived').textContent = fmt(Math.min(received, total));

        const balanceLine = document.getElementById('cfBalanceLine');
        balanceLine.className = 'small fw-semibold ' + (balance > 0 ? 'text-danger' : 'text-success');
        balanceLine.textContent = balance > 0
            ? 'Still owes TZS ' + fmt(balance)
            : 'Fully paid';
        document.getElementById('cfOverpaid').classList.toggle('d-none', received <= total);

        modal.show();
    });

    // Enter in a field opens the review instead of settling straight away.
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT' && !modalEl.contains(e.target)) {
            e.preventDefault();
            document.getElementById('settleReview').click();
        }
    });

    // Stop double submits while the settlement saves.
    form.addEventListener('submit', function () {
        const btn = document.getElementById('settleSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Settling...';
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('settleForm');
    const received = document.getElementById('amountReceived');
    const fmt = n => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function recalc() {
        let total = 0;
        form.querySelectorAll('tbody tr[data-taken]').forEach(function (row) {
            const taken = parseInt(row.dataset.taken, 10);
            const returned = Math.min(Math.max(parseInt(row.querySelector('.js-returned').value, 10) || 0, 0), taken);
            const price = parseFloat(row.querySelector('.js-price').value) || 0;
            const sold = taken - returned;
            row.querySelector('.js-sold').textContent = sold;
            row.querySelector('.js-amount').textContent = fmt(sold * price);
            total += sold * price;
        });
        document.getElementById('settleTotal').textContent = fmt(total);
        const balance = Math.max(total - (parseFloat(received.value) || 0), 0);
        const el = document.getElementById('settleBalance');
        el.textContent = fmt(balance);
        el.classList.toggle('text-danger', balance > 0);
    }

    form.addEventListener('input', recalc);
    recalc();
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\distributions\_show_scripts.blade.php ENDPATH**/ ?>