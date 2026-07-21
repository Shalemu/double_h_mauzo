<!-- MAIN CONTENT -->
<div class="container-fluid mt-4">

    <!-- SALES REPORT CARD -->
    <div class="card shadow-sm w-100">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Report for <?php echo e($shop->name ?? 'Double H Cosmetics Shop'); ?></h5>
            <!-- Back Button -->
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-sm btn-secondary">
                &larr; Back
            </a>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- EXPORT + SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between flex-wrap align-items-center">
                    <!-- Export buttons -->
                    <div class="btn-group mb-2">
                        <button type="button" class="btn btn-success btn-sm" id="export-excel">Export Excel</button>
                        <button type="button" class="btn btn-danger btn-sm" id="export-pdf">Export PDF</button>
                    </div>

                    <!-- Search input -->
                    <div class="mb-2" style="width: 250px;">
                        <input type="text" id="table-search" class="form-control form-control-sm" placeholder="Search sale...">
                    </div>
                </div>
            </div>

            <!-- SALES TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center" id="sales-table">
                    <thead class="table-primary align-middle">
                        <tr>
                            <th style="width:5%;">SN</th>
                            <th style="width:30%;">Date</th>
                            <th style="width:20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $salesByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $saleData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            
                            <td>
                            <a href="javascript:void(0)" class="view-date" data-url="<?php echo e(route('admin.sales.detail', ['shop' => $shop->id, 'date' => $saleData['date']])); ?>" style="text-decoration: none;">
                                <?php echo e($saleData['date']); ?>

                            </a>
                        </td>

                            </td>
                            <td><?php echo e(number_format($saleData['total'], 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3">No sales for this date.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<!-- Optional: Table Search JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('table-search');
    const table = document.getElementById('sales-table');
    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();

        rows.forEach(row => {
            const cellsText = Array.from(row.cells).map(td => td.textContent.toLowerCase()).join(' ');
            row.style.display = cellsText.includes(query) ? '' : 'none';
        });
    });
});

document.querySelectorAll('.view-date').forEach(link => {
    link.addEventListener('click', function() {
        const url = this.dataset.url;
        fetch(url)
            .then(res => res.text())
            .then(html => {
                document.getElementById('sale-section').innerHTML = html;
            });
    });
});


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

</script>


<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/sales/index.blade.php ENDPATH**/ ?>