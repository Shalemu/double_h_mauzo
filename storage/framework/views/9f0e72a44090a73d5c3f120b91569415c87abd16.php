
                                <div class="card mb-2 product-card"
                                     data-id="<?php echo e($product->id); ?>"
                                     data-name="<?php echo e(strtolower($product->name)); ?>"
                                     data-barcode="<?php echo e($product->barcode); ?>"
                                     data-retail-price="<?php echo e($product->selling_price ?? 0); ?>"
                                     data-wholesale-price="<?php echo e($product->wholesale_price ?? 0); ?>"
                                     data-price="<?php echo e($product->selling_price ?? 0); ?>"
                                     data-stock="<?php echo e($product->quantity ?? 0); ?>">

                                    <div class="card-body py-2">
                                        <div class="row align-items-center gy-2">
                                            <div class="col-3 col-sm-2">
                                                <img src="<?php echo e($product->image ? asset('storage/'.$product->image) : asset('assets/img/product-placeholder.png')); ?>" class="img-fluid rounded">
                                            </div>
                                            <div class="col-9 col-sm-4">
                                                <strong><?php echo e($product->name); ?></strong><br>
                                                <small class="text-muted product-price-label">Tsh <?php echo e(number_format($product->selling_price ?? 0)); ?></small><br>
                                                <small class="text-muted">Stock: <?php echo e($product->quantity ?? 0); ?> | Barcode: <?php echo e($product->barcode ?? 'N/A'); ?></small>
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
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/components/pos_product_card.blade.php ENDPATH**/ ?>