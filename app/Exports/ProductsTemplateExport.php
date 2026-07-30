<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class ProductsTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            // Example row for user guidance.
            // Unit must match an existing unit's name or short_name (e.g. "piece" / "pc") — it is
            // the unit the item is counted/sold in, not the bottle/pack size. Put the size there instead.
            [
                'Coconut Body Oil',
                'Palmers',
                '',
                '',
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
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
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
        ];
    }
}