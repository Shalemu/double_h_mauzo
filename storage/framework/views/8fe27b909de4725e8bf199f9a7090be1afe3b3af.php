<script>
document.addEventListener('DOMContentLoaded', function () {
    const lines = Array.from(document.querySelectorAll('.return-line'));
    const fmt = n => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function refresh() {
        let qty = 0, amount = 0;
        lines.forEach(function (line) {
            const q = parseInt(line.querySelector('.return-qty').value, 10) || 0;
            const a = parseFloat(line.querySelector('.return-amount').value) || 0;
            qty += q; amount += a;
            line.classList.toggle('table-warning', q > 0);
        });
        document.getElementById('sumQty').textContent = qty;
        document.getElementById('sumAmount').textContent = fmt(amount);
        document.getElementById('submitReturn').disabled = qty === 0;
    }

    lines.forEach(function (line) {
        const qty = line.querySelector('.return-qty');
        const amount = line.querySelector('.return-amount');
        const unit = parseFloat(line.dataset.unit) || 0;
        let amountEdited = amount.value !== '';

        function setQty(value) {
            const max = parseInt(qty.max, 10) || 0;
            qty.value = Math.max(0, Math.min(max, value));
            // Suggest the refund from the price paid until it's typed by hand.
            if (!amountEdited) amount.value = qty.value > 0 ? (unit * qty.value).toFixed(2) : '';
            refresh();
        }

        qty.addEventListener('input', () => setQty(parseInt(qty.value, 10) || 0));
        amount.addEventListener('input', () => { amountEdited = amount.value !== ''; refresh(); });
        line.querySelectorAll('.qty-step').forEach(btn => btn.addEventListener('click', () => setQty((parseInt(qty.value, 10) || 0) + parseInt(btn.dataset.step, 10))));
    });

    const form = document.getElementById('returnForm');
    let confirmed = false;
    form.addEventListener('submit', function (e) {
        if (confirmed) return;
        e.preventDefault();

        const qty = document.getElementById('sumQty').textContent;
        const amount = document.getElementById('sumAmount').textContent;
        const reason = (form.querySelector('[name=reason]:checked') || {}).value || '';

        Swal.fire({
            icon: 'question',
            title: 'Record this return?',
            html: '<div class="text-start">'
                + '<div class="d-flex justify-content-between"><span>Items returning</span><strong>' + qty + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>Refund to customer</span><strong class="text-danger">Tsh ' + amount + '</strong></div>'
                + '<div class="d-flex justify-content-between"><span>Reason</span><strong></strong></div>'
                + '<p class="small text-muted mt-2 mb-0">The items go back into stock and the sale total is reduced.</p>'
                + '</div>',
            didOpen: (popup) => { popup.querySelector('.d-flex:nth-child(3) strong').textContent = reason; },
            showCancelButton: true,
            confirmButtonText: 'Yes, record return',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            reverseButtons: true,
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            confirmed = true;
            const button = document.getElementById('submitReturn');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
            form.submit();
        });
    });

    refresh();
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\returns\_sale_return_scripts.blade.php ENDPATH**/ ?>