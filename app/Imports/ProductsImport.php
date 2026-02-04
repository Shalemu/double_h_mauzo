<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Products;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Convert category/unit names to IDs
        $category_id = ProductCategory::where('name', $row['category'])->first()?->id;
    
        $unit_id = Unit::where('name', $row['unit'])->first()?->id;

        return new Products([
            'name' => $row['name'],
            'brand' => $row['brand'] ?? null,
            'category_id' => $category_id,
          
            'unit_id' => $unit_id,
            'quantity' => $row['quantity'] ?? 0,
            'min_quantity' => $row['min_quantity'] ?? 0,
            'purchase_price' => $row['purchase_price'] ?? 0,
            'selling_price' => $row['selling_price'] ?? 0,
            'barcode' => $row['barcode'] ?? null,
            'expire_date' => $row['expire_date'] ?? null,
            'size' => $row['size'] ?? null,
            'color' => $row['color'] ?? null,
            'admin_id' => Auth::id(),
            'sync_status' => 0,
        ]);
    }
}
