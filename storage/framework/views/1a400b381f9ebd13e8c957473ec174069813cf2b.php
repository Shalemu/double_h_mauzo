<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amountPaid');
    const remainingDebtEl = document.getElementById('remainingDebt');

    // Correct Blade -> JS conversion
    const totalDebt = Number("<?php echo e($totalDebt ?? 0); ?>");

    function updateRemaining() {
        let paid = parseFloat(amountInput.value) || 0;
        let remaining = Math.max(totalDebt - paid, 0);
        remainingDebtEl.textContent = remaining.toLocaleString();
    }

    amountInput.addEventListener('input', updateRemaining);

    const form = document.getElementById('recordPaymentForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        let formData = new FormData(form);

        try {
            const response = await fetch("<?php echo e(route('staff.customers.recordPayment', $customer)); ?>", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': formData.get('_token') },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                document.getElementById('totalDebt').textContent = Number(data.remaining_debt).toLocaleString();
                document.getElementById('totalPaid').textContent = Number(data.total_paid).toLocaleString();
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('recordPaymentModal')).hide();
                location.reload(); // refresh table
            } else {
                alert(data.message);
            }
        } catch(err) {
            alert("Error processing payment: " + err.message);
        }
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\staff\customers\_detail_scripts.blade.php ENDPATH**/ ?>