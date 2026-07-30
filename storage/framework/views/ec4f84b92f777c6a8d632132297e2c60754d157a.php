
<?php $__env->startSection('title', 'Sales Report'); ?>

<?php echo $__env->make('components/staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<br><br><br>
<div class="container-fluid mt-5 main-content">

    <!-- MAIN SALES CARD -->
    <div class="card shadow-sm border" style="max-width: 1400px; margin: 0 auto;">
        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Report for <?php echo e($shop->name ?? 'My Shop'); ?></h5>
        </div>

        <div class="card-body">

            <!-- SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <input type="text" id="table-search" class="form-control form-control-sm"
                           placeholder="Search date..." style="width: 250px;">
                </div>
            </div>

            <!-- SALES TABLE -->
            <div class="table-responsive" id="sales-table-container">
                <table class="table table-bordered table-hover table-sm text-center" id="sales-table">
                    <thead class="table-primary">
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Total (TZS)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $salesByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td>
                                <a href="javascript:void(0)" class="view-date"
                                   data-url="<?php echo e(route('staff.sales.detail', ['shop' => $shop->id, 'date' => $sale['date']])); ?>">
                                    <?php echo e($sale['date']); ?>

                                </a>
                            </td>
                            <td><?php echo e(number_format($sale['total'], 2)); ?></td>
                            <td>
                                <button class="btn btn-success btn-sm checkout-btn"
                                    data-url="<?php echo e(route('staff.sales.checkout', ['shop' => $shop->id])); ?>"
                                    data-date="<?php echo e($sale['date']); ?>">
                                    Checkout
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No sales found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- SALES DETAILS CARD (AJAX loaded) -->
            <div id="sales-details" class="mt-4" style="display:none;">
                <!-- AJAX content will appear here -->
            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // SEARCH
    const searchInput = document.getElementById('table-search');
    const rows = document.querySelectorAll('#sales-table tbody tr');
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // AJAX: View sales by date
    document.querySelectorAll('.view-date').forEach(link => {
        link.addEventListener('click', function() {
            const url = this.dataset.url;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    // Hide main table
                    document.getElementById('sales-table-container').style.display = 'none';

                    // Show details container with a bordered card
                    const detailsDiv = document.getElementById('sales-details');
                    detailsDiv.innerHTML = `
                        <div class="card border shadow-sm">
                            <div class="card-body">
                                ${html}
                            </div>
                        </div>
                    `;
                    detailsDiv.style.display = 'block';

                    // Re-attach sale-type filter
                    attachSaleTypeFilter();
                })
                .catch(() => alert('Failed to load sales details.'));
        });
    });

    // Checkout button (optional)
    document.querySelectorAll('.checkout-btn').forEach(button => {
        button.addEventListener('click', function() {
            alert('Checkout logic here');
        });
    });

    // Attach filter for sale-type radio buttons
    function attachSaleTypeFilter() {
        const radios = document.querySelectorAll('input[name="sale-type"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedType = this.value;
                const table = document.getElementById('sales-detail-table');
                if (!table) return;

                const tableRows = table.querySelectorAll('tbody tr[data-sale-type]');
                let anyVisible = false;

                tableRows.forEach(row => {
                    const rowType = row.dataset.saleType || 'retail';
                    if (selectedType === 'both' || rowType === selectedType) {
                        row.style.display = '';
                        anyVisible = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show or hide "No sales" row
                const noSalesRow = document.getElementById('no-sales-row');
                if (noSalesRow) noSalesRow.style.display = anyVisible ? 'none' : '';
            });
        });

        // Trigger default filter on load
        const checkedRadio = document.querySelector('input[name="sale-type"]:checked');
        if (checkedRadio) checkedRadio.dispatchEvent(new Event('change'));
    }
});
</script>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/staff/sales/index.blade.php ENDPATH**/ ?>