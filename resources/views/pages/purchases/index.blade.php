<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">

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
                <input type="text" id="purchases-table-search" class="form-control form-control-sm"
                       placeholder="Search date..." style="width:250px;">
            </div>

            <!-- AJAX FORM -->
            <div id="add-purchase-container" style="display:none;"></div>

            <!-- NEW PRODUCT FORM -->
            <div id="newProductContainer" style="display:none;"></div>

            <!-- TABLE -->
            <div id="purchases-table-container">
                <table class="table table-bordered table-sm text-center" id="purchases-table">
                    <thead class="table-primary">
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Total Paid</th>
                            <th>Remaining Credit</th>
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
                                <td>{{ number_format($data['total_amount'], 2) }}</td>
                                <td>{{ number_format($data['total_paid'] ?? 0, 2) }}</td>
                                <td class="text-danger">{{ number_format($data['remaining'] ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No purchases found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PURCHASE DETAILS -->
            <div id="purchase-details" class="mt-4"></div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const showBtn = document.getElementById('show-add-purchase');
    const tableContainer = document.getElementById('purchases-table-container');
    const formContainer = document.getElementById('add-purchase-container');
    const newProductContainer = document.getElementById('newProductContainer');

    // SEARCH
    document.getElementById('purchases-table-search').addEventListener('input', function () {
        let q = this.value.toLowerCase();
        document.querySelectorAll('#purchases-table tbody tr').forEach(row => {
            row.style.display = row.cells[1].innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // VIEW PURCHASE DETAILS
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

    // LOAD PURCHASE FORM
    showBtn.addEventListener('click', function () {
        fetch("{{ route('purchases.create') }}")
            .then(res => res.text())
            .then(html => {
                formContainer.innerHTML = html;
                formContainer.style.display = 'block';
                tableContainer.style.display = 'none';
                showBtn.style.display = 'none';

                // Initialize purchase form functions if exists
                if(typeof initPurchaseForm === 'function') initPurchaseForm();

                // Initialize "New Item" button inside purchase form
                const btnNewItem = document.getElementById('btnNewItem');
                if(btnNewItem){
                    btnNewItem.addEventListener('click', function(e){
                        e.preventDefault();
                        loadNewProductForm();
                    });
                }

                // CANCEL PURCHASE FORM
                const cancelBtn = document.getElementById('cancel-add-purchase');
                if(cancelBtn){
                    cancelBtn.onclick = function () {
                        formContainer.innerHTML = '';
                        formContainer.style.display = 'none';
                        tableContainer.style.display = 'block';
                        showBtn.style.display = 'inline-block';
                        newProductContainer.style.display = 'none';
                        newProductContainer.innerHTML = '';
                    };
                }
            })
            .catch(err => console.error(err));
    });

    // LOAD NEW PRODUCT FORM
function loadNewProductForm() {
    formContainer.style.display = 'none';

    fetch("{{ route('purchases.new_product') }}")
        .then(res => res.text())
        .then(html => {
            newProductContainer.innerHTML = html;
            newProductContainer.style.display = 'block';

            initNewProductForm(); // VERY IMPORTANT
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Failed to load form', 'error');
        });
}

});
</script>