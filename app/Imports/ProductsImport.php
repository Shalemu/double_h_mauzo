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
    $category = ProductCategory::whereRaw('LOWER(name) = ?', [strtolower(trim($row['category']))])->first();
    $unit = Unit::whereRaw('LOWER(name) = ?', [strtolower(trim($row['unit']))])->first();

    if (!$unit) {
        return null; // skip if unit not found
    }

    $sale_type = strtolower(trim($row['sale_type'] ?? 'retail'));
    if (!in_array($sale_type, ['retail', 'wholesale', 'both'])) {
        $sale_type = 'retail';
    }


    $expire_date = null;
    if (!empty($row['expire_date'])) {
        try {
            $date = \Carbon\Carbon::parse($row['expire_date']);
            $expire_date = $date->format('Y-m-d'); // ensure MySQL compatible
        } catch (\Exception $e) {
            $expire_date = null; // invalid date → leave null
        }
    }
    $shopId = Auth::user()->shop->id ?? null; // admin shop id

    return new Products([
        'name' => $row['name'],
        'brand' => $row['brand'] ?? null,
        'category_id' => $category?->id,
        'unit_id' => $unit->id,
        'quantity' => $row['quantity'] ?? 0,
        'min_quantity' => $row['min_quantity'] ?? 0,
        'purchase_price' => $row['purchase_price'] ?? 0,
        'selling_price' => $row['selling_price'] ?? 0,
        'barcode' => $row['barcode'] ?? null,
        'expire_date' => $expire_date,
        'size' => $row['size'] ?? null,
        'color' => $row['color'] ?? null,
        'sale_type' => $sale_type,
        'admin_id' => Auth::id(),
         'shop_id' => $shopId, 
        'sync_status' => 0,
    ]);
}
}
