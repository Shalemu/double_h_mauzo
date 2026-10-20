<?php

namespace App\DTO\Products;

use App\DTO\DataTransferObject;
use Illuminate\Http\UploadedFile;

/**
 * A new product from the Add Product page.
 */
class ProductData extends DataTransferObject
{
    /** Product fields the form may set. */
    public const FIELDS = [
        'name', 'item_code', 'barcode', 'brand', 'category_id', 'subcategory_id', 'unit_id',
        'quantity', 'min_quantity', 'purchase_price', 'selling_price', 'wholesale_price',
        'invoice_number', 'expire_date', 'size', 'color',
    ];

    public function __construct(
        public readonly int $shopId,
        public readonly array $attributes,
        public readonly ?UploadedFile $image = null,
        public readonly ?int $adminId = null,
    ) {
    }

    public static function fromArray(int $shopId, array $input, ?UploadedFile $image = null, ?int $adminId = null): self
    {
        return new self($shopId, array_intersect_key($input, array_flip(self::FIELDS)), $image, $adminId);
    }
}
