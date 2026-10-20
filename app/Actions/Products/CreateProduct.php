<?php

namespace App\Actions\Products;

use App\DTO\Products\ProductData;
use App\Models\Products;

/**
 * Create a product in a shop/store, storing its photo if one was uploaded.
 */
class CreateProduct
{
    public function execute(ProductData $data): Products
    {
        $attributes = $data->attributes + [
            'shop_id' => $data->shopId,
            'admin_id' => $data->adminId,
            'sync_status' => 0,
        ];

        if ($data->image) {
            $attributes['image'] = $data->image->store('products', 'public');
        }

        return Products::create($attributes);
    }
}
