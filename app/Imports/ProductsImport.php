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
    /**
     * Human-readable notes (skips, auto-created records, etc.) shown back to the user.
     */
    public array $errors = [];

    // Row 1 is the heading row, so data starts at row 2.
    protected int $rowNumber = 1;

    public function model(array $row)
    {
        $this->rowNumber++;
        $label = "Row {$this->rowNumber}";

        $name = trim($row['name'] ?? '');
        if ($name === '') {
            $this->errors[] = "{$label}: skipped — Name is missing.";
            return null;
        }
        $label .= " ({$name})";

        $unitValue = trim($row['unit'] ?? '');
        if ($unitValue === '') {
            $this->errors[] = "{$label}: skipped — Unit is missing.";
            return null;
        }

        $shopId = Auth::user()->shop->id ?? null; // admin shop id

        $unit = Unit::whereRaw('LOWER(name) = ?', [strtolower($unitValue)])
            ->orWhereRaw('LOWER(short_name) = ?', [strtolower($unitValue)])
            ->first();

        if (!$unit) {
            $unit = Unit::create([
                'name' => $unitValue,
                'short_name' => mb_substr($unitValue, 0, 10),
                'shop_id' => $shopId,
                'admin_id' => Auth::id(),
            ]);
            $this->errors[] = "{$label}: unit \"{$unitValue}\" didn't exist — added it to Units automatically.";
        }

        $categoryValue = trim($row['category'] ?? '');
        $category = null;
        if ($categoryValue !== '') {
            $category = ProductCategory::whereRaw('LOWER(name) = ?', [strtolower($categoryValue)])->first();

            if (!$category) {
                $category = ProductCategory::create([
                    'name' => $categoryValue,
                    'shop_id' => $shopId,
                    'admin_id' => Auth::id(),
                ]);
                $this->errors[] = "{$label}: category \"{$categoryValue}\" didn't exist — added it automatically.";
            }
        }

        $expire_date = null;
        if (!empty($row['expire_date'])) {
            try {
                $expire_date = \Carbon\Carbon::parse($row['expire_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $this->errors[] = "{$label}: expire date \"{$row['expire_date']}\" is invalid and was ignored.";
            }
        }

        return new Products([
            'name' => $name,
            'brand' => $row['brand'] ?? null,
            'category_id' => $category?->id,
            'unit_id' => $unit->id,
            'quantity' => $row['quantity'] ?? 0,
            'min_quantity' => $row['min_quantity'] ?? 0,
            'purchase_price' => $row['purchase_price'] ?? 0,
            'selling_price' => $row['selling_price'] ?? 0,
            'wholesale_price' => $row['wholesale_price'] ?? 0,
            'barcode' => $row['barcode'] ?? null,
            'expire_date' => $expire_date,
            'size' => $row['size'] ?? null,
            'color' => $row['color'] ?? null,
            'admin_id' => Auth::id(),
            'shop_id' => $shopId,
            'sync_status' => 0,
        ]);
    }
}
