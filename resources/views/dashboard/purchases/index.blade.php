<div class="container-fluid mt-4 main-content">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Purchases Report for {{ $shop->name ?? 'Shop' }}
            </h5>

            <button id="show-add-purchase" class="btn btn-primary btn-sm">
                + Add Purchase
            </button>
        </div>

        <div class="card-body">

            <!-- SEARCH -->
            <div class="d-flex justify-content-end mb-3">
                <input type="text" id="table-search" class="form-control form-control-sm"
                       placeholder="Search date..." style="width:250px;">
            </div>

            <!-- AJAX FORM -->
            <div id="add-purchase-container" style="display:none;"></div>

            <!-- TABLE -->
            <div id="purchases-table-container">
                <table class="table table-bordered table-sm text-center" id="purchases-table">
                    <thead class="table-primary">
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchasesByDate as $date => $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="#" class="view-date"
                                   data-url="{{ route('purchases.detail', ['shop'=>$shop->id,'date'=>$data['date']]) }}">
                                   {{ $data['date'] }}
                                </a>
                            </td>
                            <td>{{ number_format($data['total'],2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="purchase-details" class="mt-4"></div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const showBtn = document.getElementById('show-add-purchase');
    const tableContainer = document.getElementById('purchases-table-container');
    const formContainer = document.getElementById('add-purchase-container');

    // SEARCH
    document.getElementById('table-search').addEventListener('input', function () {
        let q = this.value.toLowerCase();
        document.querySelectorAll('#purchases-table tbody tr').forEach(row => {
            row.style.display = row.cells[1].innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // VIEW DETAILS
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('view-date')) {
            e.preventDefault();

            fetch(e.target.dataset.url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('purchase-details').innerHTML = html;
                });
        }
    });

    // LOAD FORM
    showBtn.addEventListener('click', function () {

        fetch("{{ route('purchases.create') }}")
            .then(res => res.text())
            .then(html => {

                formContainer.innerHTML = html;
                formContainer.style.display = 'block';
                tableContainer.style.display = 'none';
                showBtn.style.display = 'none';

                // IMPORTANT
                initPurchaseForm();

                // CANCEL BUTTON
                document.getElementById('cancel-add-purchase').onclick = function () {
                    formContainer.innerHTML = '';
                    formContainer.style.display = 'none';
                    tableContainer.style.display = 'block';
                    showBtn.style.display = 'inline-block';
                };
            });
    });

});
</script>


