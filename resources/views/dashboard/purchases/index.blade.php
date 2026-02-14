<div class="container-fluid mt-4 main-content">

    <!-- PURCHASES REPORT CARD -->
    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Purchases Report for {{ $shop->name ?? 'Double H Cosmetics Shop' }}</h5>
            <button id="show-add-purchase" class="btn btn-primary btn-sm">
                + Add Purchase
            </button>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- SEARCH -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <input type="text" id="table-search" class="form-control form-control-sm" placeholder="Search date..." style="width: 250px;">
                </div>
            </div>

            <!-- CONTAINER FOR ADD PURCHASE FORM -->
            <div id="add-purchase-container" style="display: none;"></div>

            <!-- PURCHASES TABLE -->
            <div class="table-responsive" id="purchases-table-container">
                <table class="table table-bordered table-hover table-sm text-center" id="purchases-table">
                    <thead class="table-primary align-middle">
                        <tr>
                            <th style="width:5%;">SN</th>
                            <th style="width:30%;">Date</th>
                            <th style="width:20%;">Total (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchasesByDate as $date => $purchaseData)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="javascript:void(0)" class="view-date" 
                                   data-url="{{ route('purchases.detail', ['shop' => $shop->id, 'date' => $purchaseData['date']]) }}">
                                    {{ $purchaseData['date'] }}
                                </a>
                            </td>
                            <td>{{ number_format($purchaseData['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No purchases found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- DETAILED PURCHASE SECTION -->
            <div id="purchase-details" class="mt-4"></div>

        </div>
    </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ------------------------------
    // Table search
    // ------------------------------
    const searchInput = document.getElementById('table-search');
    const table = document.getElementById('purchases-table');
    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // ------------------------------
    // View purchases by date
    // ------------------------------
    document.querySelectorAll('.view-date').forEach(link => {
        link.addEventListener('click', function() {
            const url = this.dataset.url;
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('purchase-details').innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to load purchases for this date.');
                });
        });
    });

    // ------------------------------
    // Load Add Purchase form via AJAX
    // ------------------------------
    const showBtn = document.getElementById('show-add-purchase');
    const tableContainer = document.getElementById('purchases-table-container');
    const formContainer = document.getElementById('add-purchase-container');

    showBtn.addEventListener('click', function() {
       fetch("{{ route('purchases.create') }}")

            .then(res => res.text())
            .then(html => {
                formContainer.innerHTML = html;
                formContainer.style.display = 'block';
                tableContainer.style.display = 'none';
                showBtn.style.display = 'none';

                // Attach Cancel button inside loaded form
                const cancelBtn = formContainer.querySelector('#cancel-add-purchase');
                if(cancelBtn) {
                    cancelBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        formContainer.innerHTML = '';
                        formContainer.style.display = 'none';
                        tableContainer.style.display = 'block';
                        showBtn.style.display = 'inline-block';
                    });
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to load add purchase form.');
            });
    });

});
</script>
