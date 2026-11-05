{{-- One POS product card. Used by pos_core and by the POS search endpoint. --}}
                                <div class="card mb-2 product-card"
                                     data-id="{{ $product->id }}"
                                     data-name="{{ strtolower($product->name) }}"
                                     data-barcode="{{ $product->barcode }}"
                                     data-retail-price="{{ $product->selling_price ?? 0 }}"
                                     data-wholesale-price="{{ $product->wholesale_price ?? 0 }}"
                                     data-price="{{ $product->selling_price ?? 0 }}"
                                     data-stock="{{ $product->quantity ?? 0 }}">

                                    <div class="card-body py-2">
                                        <div class="row align-items-center gy-2">
                                            <div class="col-3 col-sm-2">
                                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('assets/img/product-placeholder.png') }}" class="img-fluid rounded">
                                            </div>
                                            <div class="col-9 col-sm-4">
                                                <strong>{{ $product->name }}</strong><br>
                                                <small class="text-muted product-price-label">Tsh {{ number_format($product->selling_price ?? 0) }}</small><br>
                                                <small class="text-muted">Stock: {{ $product->quantity ?? 0 }} | Barcode: {{ $product->barcode ?? 'N/A' }}</small>
                                            </div>
                                            <div class="col-12 col-sm-3 d-flex align-items-center">
                                                <button class="btn btn-sm btn-outline-secondary qty-minus">−</button>
                                                <input type="number" class="form-control form-control-sm mx-1 qty-input" value="0" min="0" style="width:60px;">
                                                <button class="btn btn-sm btn-outline-secondary qty-plus">+</button>
                                            </div>
                                            <div class="col-12 col-sm-3">
                                                <input type="number" class="form-control form-control-sm discount-input" placeholder="Discount" value="0">
                                                <button class="btn btn-sm btn-primary mt-1 w-100 add-to-cart d-none">Add to Cart</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
