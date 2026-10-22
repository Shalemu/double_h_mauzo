<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.view-order-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const order = JSON.parse(this.getAttribute('data-order'));

            // Format staff name
            const staffName = order.staff?.full_name ?? 'N/A';
            const itemsTotal = order.items.reduce((sum, i) => sum + Number(i.total), 0);
            const totalAmount = Number(order.total_amount) || itemsTotal;

            // Build HTML
            let html = `
                <p><strong>Staff:</strong> ${staffName}</p>
                <p><strong>Status:</strong> ${order.status}</p>
                <p><strong>Total:</strong> Tsh ${totalAmount.toLocaleString()}</p>
                <hr>
                <h6>Items</h6>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            order.items.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.product?.name ?? 'N/A'}</td>
                        <td>${item.quantity}</td>
                        <td>Tsh ${Number(item.price).toLocaleString()}</td>
                        <td>Tsh ${Number(item.total).toLocaleString()}</td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;

            document.getElementById('orderDetailsContent').innerHTML = html;

            new bootstrap.Modal(document.getElementById('orderModal')).show();
        });
    });
});
</script><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\orders\_index_scripts.blade.php ENDPATH**/ ?>