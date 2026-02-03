<div class="container-fluid mt-4">

    <div class="summary-card">

        <!-- HEADER -->
        <div class="summary-header">
            <h5>Shop Summary</h5>
            <small>Muhtasari wa mauzo, matumizi na faida</small>
        </div>

        <!-- TABLES -->
        <div class="summary-body">

            <!-- TODAY -->
            <div class="summary-table">
                <h6>Today</h6>
                <table>
                    <tr>
                        <td>Sales</td>
                        <td class="text-end">0</td>
                    </tr>
                    <tr>
                        <td>Expenses</td>
                        <td class="text-end">0</td>
                    </tr>
                    <tr class="profit">
                        <td>Profit</td>
                        <td class="text-end">0</td>
                    </tr>
                </table>
            </div>

            <!-- THIS MONTH -->
            <div class="summary-table">
                <h6>{{ now()->format('Y, M') }}</h6>
                <table>
                    <tr>
                        <td>Sales</td>
                        <td class="text-end">49,661,400</td>
                    </tr>
                    <tr>
                        <td>Expenses</td>
                        <td class="text-end">258</td>
                    </tr>
                    <tr class="profit">
                        <td>Profit</td>
                        <td class="text-end">16,574,000</td>
                    </tr>
                </table>
            </div>

            <!-- CURRENT STOCK -->
            <div class="summary-table">
                <h6>Current Stock</h6>
                <table>
                    <tr>
                        <td>Capital</td>
                        <td class="text-end">217,515,600</td>
                    </tr>
                    <tr>
                        <td>Sales</td>
                        <td class="text-end">250,907,800</td>
                    </tr>
                    <tr class="profit">
                        <td>Profit</td>
                        <td class="text-end">33,392,200</td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

</div>


<style>
    .summary-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 20px;
}

/* Header */
.summary-header {
    margin-bottom: 20px;
}

.summary-header h5 {
    margin: 0;
    font-weight: 600;
}

.summary-header small {
    color: #6b7280;
}

/* Tables layout */
.summary-body {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

/* Each table */
.summary-table {
    background: #fff7ed;
    border-radius: 8px;
    padding: 15px;
}

.summary-table h6 {
    font-weight: 600;
    margin-bottom: 10px;
    color: #f97316;
}

/* Table */
.summary-table table {
    width: 100%;
    font-size: 14px;
}

.summary-table td {
    padding: 6px 0;
}

.summary-table .profit {
    font-weight: 600;
}

/* Responsive */
@media (max-width: 992px) {
    .summary-body {
        grid-template-columns: 1fr;
    }
}

</style>
