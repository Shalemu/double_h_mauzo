@section('title', 'Sales Report')
@include('main')
@include('components/staff_header')
@include('components/mainmenu')

<div class="container-fluid mt-4 main-content">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <!-- HEADER -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Sales Report for {{ $shop->name ?? 'My Shop' }}
            </h5>
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
                        @forelse ($salesByDate as $sale)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="javascript:void(0)" class="view-date"
                                   data-url="{{ route('staff.sales.detail', ['shop' => $shop->id, 'date' => $sale['date']]) }}">
                                    {{ $sale['date'] }}
                                </a>
                            </td>
                            <td>{{ number_format($sale['total'], 2) }}</td>
                            <td>
                                <button class="btn btn-success btn-sm checkout-btn"
                                    data-url="{{ route('staff.sales.checkout', ['shop' => $shop->id]) }}"
                                    data-date="{{ $sale['date'] }}">
                                    Checkout
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">No sales found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- DETAILS -->
            <div id="sales-details" class="mt-4" style="display:none;"></div>

        </div>
    </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // 🔎 Search in table
    const searchInput = document.getElementById('table-search');
    const rows = document.querySelectorAll('#sales-table tbody tr');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.cells[1].textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // 📅 AJAX: View sales by date
    document.querySelectorAll('.view-date').forEach(link => {
        link.addEventListener('click', function() {
            const url = this.dataset.url;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    // Hide main table
                    document.getElementById('sales-table-container').style.display = 'none';

                    // Show details container
                    const detailsDiv = document.getElementById('sales-details');
                    detailsDiv.innerHTML = html;
                    detailsDiv.style.display = 'block';
                })
                .catch(() => alert('Failed to load sales details.'));
        });
    });

    // 💰 AJAX: Checkout
    document.querySelectorAll('.checkout-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const date = this.dataset.date;

            // You can customize the cart payload as needed
            const cartData = []; // empty for demonstration, replace with real cart if needed

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ cart: cartData })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Checkout completed successfully!');
                    location.reload(); // reload to refresh totals
                } else {
                    alert('Checkout failed: ' + data.message);
                }
            })
            .catch(err => alert('Checkout request failed.'));
        });
    });

});
</script>
