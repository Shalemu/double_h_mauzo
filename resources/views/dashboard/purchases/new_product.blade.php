<form id="newProductForm" enctype="multipart/form-data" novalidate>
    
    @csrf

    <div class="row g-3">

        <!-- Shop Selection -->
        <div class="col-md-6">
            <label>Shop</label>
            <select name="shop_id" class="form-select" required>
                <option value="">Select Shop</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Product Name -->
        <div class="col-md-6">
            <label>Product Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <!-- Category -->
        <div class="col-md-6">
            <label>Category</label>
            <select name="category_id" class="form-select">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Unit -->
        <div class="col-md-6">
            <label>Unit</label>
            <select name="unit_id" class="form-select" required>
                <option value="">Select Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Quantity -->
        <div class="col-md-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
        </div>

        <!-- Purchase Price -->
        <div class="col-md-3">
            <label>Purchase Price</label>
            <input type="number" name="purchase_price" class="form-control" min="0" step="0.01" required>
        </div>

        <!-- Selling Price -->
        <div class="col-md-3">
            <label>Selling Price</label>
            <input type="number" name="selling_price" class="form-control" min="0" step="0.01">
        </div>

        <!-- Supplier -->
        <div class="col-md-6">
            <label>Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Payment Type -->
        <div class="col-md-6">
            <label>Payment Type</label>
            <select name="payment_type" id="paymentType" class="form-select" required>
                <option value="">Select Payment Type</option>
                <option value="cash">Cash</option>
                <option value="credit">Credit</option>
            </select>
        </div>

        <!-- Initial Deposit -->
        <div class="col-md-6" id="amountPaidBox" style="display:none;">
            <label>Initial Deposit</label>
            <input type="number" name="amount_paid" id="amountPaid" class="form-control" value="0">
            <small id="remainingCredit" class="text-danger mt-1 d-block">
                Remaining Credit: 0.00
            </small>
        </div>

        <!-- Image -->
        <div class="col-md-6">
            <label>Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
    </div>

    <!-- Buttons -->
    <div class="mt-3 text-end">
        <button type="button" class="btn btn-secondary" id="cancelNewProduct">Back</button>
        <button type="button" class="btn btn-success" id="submitNewProduct">Add & Purchase</button>
    </div>
</form>