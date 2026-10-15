{{--
    Add (fixed) expense modal. The store routes take the shop in the URL,
    so the chosen shop rewrites the form action before submit.
--}}
@php $actionTemplate = route($routeName, '__SHOP__'); @endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" enctype="multipart/form-data"
              data-action-template="{{ $actionTemplate }}"
              action="{{ $shopId ? str_replace('__SHOP__', $shopId, $actionTemplate) : '' }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Title">{{ $heading }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Shop</label>
                    <select class="form-select expense-shop" required>
                        <option value="">Choose shop...</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ ((string) $shopId === (string) $shop->id) ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount (Tsh)</label>
                    <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control" rows="2"></textarea>
                </div>
                @if($withReceipt)
                    <div class="mb-3">
                        <label class="form-label">Receipt (optional, jpg/png/pdf, max 2MB)</label>
                        <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#{{ $modalId }} form');
    const shop = form.querySelector('.expense-shop');
    shop.addEventListener('change', function () {
        form.action = shop.value ? form.dataset.actionTemplate.replace('__SHOP__', shop.value) : '';
    });
});
</script>
