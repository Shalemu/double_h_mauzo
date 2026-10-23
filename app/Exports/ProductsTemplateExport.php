<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

/**
 * Sample sheet for the product import (App\Imports\ProductsImport).
 *
 * - Name and Unit are required for a new product. A row whose Barcode — or
 *   else Name — already exists in the shop updates that product instead of
 *   adding a copy; empty cells leave its current values as they are.
 * - Unit must match an existing unit's name or short_name (e.g. "piece" / "pc"):
 *   the unit the item is counted/sold in, not the bottle/pack size (that goes in Size).
 * - Subcategory sits under the Category in the same row.
 * - Quantity is the stock count in that shop.
 */
class ProductsTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            // Example row for user guidance.
            [
                'Coconut Body Oil',
                'CBO-450',
                'Palmers',
                'Bath & Body',
                'Body Oil',
                'piece',
                10,
                2,
                3000,
                4000,
                3500,
                '123456789',
                '2026-12-31',
                '450ml',
                '',
                'Coconut oil for dry skin',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Item Code',
            'Brand',
            'Category',
            'Subcategory',
            'Unit',
            'Quantity',
            'Min Quantity',
            'Purchase Price',
            'Selling Price',
            'Wholesale Price',
            'Barcode',
            'Expire Date',
            'Size',
            'Color',
            'Description',
        ];
    }
}
