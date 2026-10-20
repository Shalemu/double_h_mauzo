<?php

namespace App\Actions\Procurement;

use App\DTO\Procurement\SupplierAssignmentData;
use App\Models\Products;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\DB;

/**
 * Link a product to a supplier (or update that supplier's price). The
 * supplier price also becomes the product's purchasing price — what Price
 * Manager shows — when the user is allowed to change prices.
 */
class AssignProductToSupplier
{
    /**
     * @return array{assignment: SupplierProduct, product: Products, price: float, old_cost: float, cost_updated: bool}
     */
    public function execute(SupplierAssignmentData $data, bool $canChangePrices, ?int $assignedBy = null): array
    {
        $product = Products::findOrFail($data->productId);
        $oldCost = (float) $product->purchase_price;
        $price = $data->newPrice ?? $oldCost;
        $syncCost = $price !== $oldCost && $canChangePrices;

        $assignment = DB::transaction(function () use ($data, $product, $price, $syncCost, $assignedBy) {
            $assignment = SupplierProduct::updateOrCreate(
                ['supplier_id' => $data->supplierId, 'product_id' => $product->id],
                ['supplier_price' => $price, 'assigned_by' => $assignedBy]
            );

            if ($syncCost) {
                $product->update(['purchase_price' => $price]);
            }

            return $assignment;
        });

        return ['assignment' => $assignment, 'product' => $product, 'price' => $price, 'old_cost' => $oldCost, 'cost_updated' => $syncCost];
    }
}
