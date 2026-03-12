<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class ProductsTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            // Example row for user guidance
            [
                'Product 1',
                'Brand',
                'Category',
                'Subcategory',
                'Piece',
                10,
                2,
                1000,
                1200,
                '123456789',
                '2026-12-31',
                'Medium',
                'Red',
                'retail' // sale_type example
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
            'Barcode',
            'Expire Date',
            'Size',
            'Color',
            'Sale Type'
        ];
    }
}