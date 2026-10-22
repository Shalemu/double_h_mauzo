<script>
document.addEventListener('DOMContentLoaded', function () {
    const fmt = n => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const rows = document.querySelectorAll('#receiveTable tbody tr');
    const paymentType = document.getElementById('paymentType');
    const amountPaid = document.getElementById('amountPaid');
    let total = 0;

    function refresh() {
        total = 0;
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.js-qty').value) || 0;
            const price = parseFloat(row.querySelector('.js-price').value) || 0;
            const ordered = parseFloat(row.dataset.ordered) || 0;
            const orderPrice = parseFloat(row.dataset.orderPrice) || 0;
            total += qty * price;
            row.querySelector('.js-line-total').textContent = fmt(qty * price);

            // Flag differences from the order so they're confirmed on purpose.
            const qtyNote = row.querySelector('.js-qty-note');
            qtyNote.textContent = qty < ordered ? (ordered - qty) + ' short' : qty > ordered ? (qty - ordered) + ' extra' : '';
            qtyNote.className = 'small js-qty-note ' + (qty < ordered ? 'text-danger' : 'text-primary');
            const priceNote = row.querySelector('.js-price-note');
            priceNote.textContent = price !== orderPrice ? 'ordered at ' + fmt(orderPrice) : '';
            priceNote.className = 'small js-price-note text-warning-emphasis';
        });
        document.getElementById('receivedTotal').textContent = fmt(total);

        const credit = paymentType.value === 'credit';
        document.getElementById('depositBox').style.display = credit ? '' : 'none';
        const paid = Math.min(total, parseFloat(amountPaid.value) || 0);
        document.getElementById('owedNote').textContent = credit ? 'Still owed after receipt: ' + fmt(total - paid) : '';
    }

    const form = document.getElementById('receiveForm');
    const shopName = <?php echo json_encode($invoice->shop->name ?? 'the shop', 15, 512) ?>;
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const warn = (title, text, focus) => Swal.fire({ icon: 'warning', title: title, text: text })
        .then(() => focus && focus.focus());

    form.addEventListener('input', refresh);
    paymentType.addEventListener('change', refresh);

    // Enter in a field must not send the form without the confirmation.
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.preventDefault();
    });

    document.getElementById('confirmReceive').addEventListener('click', function () {
        refresh();

        // Check every line and point at the first problem.
        for (const row of rows) {
            const name = row.dataset.name;
            const qtyInput = row.querySelector('.js-qty');
            const priceInput = row.querySelector('.js-price');
            const qty = qtyInput.value.trim();
            if (qty === '' || !/^\d+$/.test(qty)) {
                return warn('Received quantity needed', 'Enter how many "' + name + '" arrived (0 if none came).', qtyInput);
            }
            if (priceInput.value.trim() === '' || Number(priceInput.value) < 0) {
                return warn('Buying price needed', 'Enter the buying price for "' + name + '".', priceInput);
            }
        }
        if (![...rows].some(r => Number(r.querySelector('.js-qty').value) > 0)) {
            return warn('Nothing received', 'Enter the quantities that arrived, or cancel the order instead.');
        }

        // Summary of what will be recorded, with anything different from the order called out.
        let lines = '';
        rows.forEach(row => {
            const qty = Number(row.querySelector('.js-qty').value);
            const price = Number(row.querySelector('.js-price').value);
            const ordered = Number(row.dataset.ordered);
            const orderPrice = Number(row.dataset.orderPrice);
            const notes = [];
            if (qty !== ordered) notes.push(qty < ordered ? (ordered - qty) + ' short' : (qty - ordered) + ' extra');
            if (price !== orderPrice) notes.push('price ' + fmt(orderPrice) + ' → ' + fmt(price));
            lines += '<tr><td class="text-start">' + esc(row.dataset.name) + '</td><td class="text-end">' + qty + ' / ' + ordered + '</td>'
                + '<td class="text-end">' + fmt(qty * price) + '</td><td class="text-start small text-danger">' + esc(notes.join(', ')) + '</td></tr>';
        });
        const credit = paymentType.value === 'credit';
        const paid = credit ? Math.min(total, Number(amountPaid.value) || 0) : total;

        Swal.fire({
            icon: 'question',
            title: 'Receive into ' + shopName + '?',
            width: 640,
            html: '<div class="table-responsive"><table class="table table-sm align-middle mb-2" style="font-size:.9rem">'
                + '<thead><tr><th class="text-start">Product</th><th class="text-end">Received / ordered</th><th class="text-end">Amount</th><th></th></tr></thead>'
                + '<tbody>' + lines + '</tbody>'
                + '<tfoot><tr><th class="text-start" colspan="2">Total</th><th class="text-end">Tsh ' + fmt(total) + '</th><th></th></tr></tfoot></table></div>'
                + '<div class="small text-muted">' + (credit ? 'Credit: paying Tsh ' + fmt(paid) + ' now, owing Tsh ' + fmt(total - paid) + '.' : 'Cash: paid in full.')
                + '<br>The stock is added to ' + esc(shopName) + ' and the purchase is recorded.</div>',
            showCancelButton: true,
            confirmButtonText: 'Yes, receive',
            cancelButtonText: 'Back to edit',
            confirmButtonColor: '#198754',
            reverseButtons: true,
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            const btn = document.getElementById('confirmReceive');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Receiving...';
            form.submit();
        });
    });

    refresh();
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\_receive_scripts.blade.php ENDPATH**/ ?>