<?php

namespace App\Actions\Procurement;

use App\Models\SupplierProduct;

/**
 * Make a supplier's price the product's purchasing price.
 */
class ApplySupplierPrice
{
    public function execute(SupplierProduct $assignment): SupplierProduct
    {
        $assignment->load('product', 'supplier');
        $assignment->product->update(['purchase_price' => $assignment->supplier_price]);

        return $assignment;
    }
}
