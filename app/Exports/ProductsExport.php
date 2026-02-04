<?php

namespace App\Exports;

use App\Models\Products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch products with related category, subcategory, and unit
     */
    public function collection()
    {
        return Products::with(['category', 'subcategory', 'unit'])->get();
    }

    /**
     * Map each product row
     */
    public function map($product): array
    {
        return [
            $product->name,
            $product->brand ?? '-',
            $product->category ? $product->category->name : '-',
            $product->unit ? $product->unit->name : '-',
            $product->quantity ?? 0,
            $product->min_quantity ?? 0,
            $product->purchase_price ?? 0,
            $product->selling_price ?? 0,
            $product->barcode ?? '-',
            $product->expire_date ?? '-',
            $product->size ?? '-',
            $product->color ?? '-',
        ];
    }

    /**
     * Define the headings for Excel
     */
    public function headings(): array
    {
        return [
            'Name',
            'Brand',
            'Category',
            'Unit',
            'Quantity',
            'Min Quantity',
            'Purchase Price',
            'Selling Price',
            'Barcode',
            'Expire Date',
            'Size',
            'Color',
        ];
    }
}
