<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Products;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Product list import (see ProductsTemplateExport for the columns).
 *
 * A row updates the product that already exists in the target shop — same
 * barcode, or else same name — instead of creating a copy, so importing the
 * same file twice is safe. Only cells that are filled in overwrite what the
 * product has; Quantity sets the stock to that number.
 */
class ProductsImport implements ToCollection, WithHeadingRow
{
    /**
     * Human-readable notes (skips, auto-created records, etc.) shown back to the user.
     */
    public array $errors = [];

    public int $created = 0;
    public int $updated = 0;

    protected ?int $shopId;

    public function __construct(?int $shopId = null)
    {
        // A super admin picks the target shop explicitly; a regular admin
        // falls back to the single shop they own.
        $this->shopId = $shopId ?? (Auth::user()->shop->id ?? null);
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                // Row 1 is the heading row, so data starts at row 2.
                $this->importRow($row->toArray(), $index + 2);
            }
        });
    }

    protected function importRow(array $row, int $rowNumber): void
    {
        $label = "Row {$rowNumber}";
        $text = fn ($key) => trim((string) ($row[$key] ?? ''));
        $number = function ($key) use ($row) {
            $value = trim(str_replace(',', '', (string) ($row[$key] ?? '')));
            return $value === '' || !is_numeric($value) ? null : $value + 0;
        };

        $name = $text('name');
        if ($name === '') {
            // Fully empty rows (e.g. formatting left at the bottom of the sheet) are skipped silently.
            if (collect($row)->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty()) {
                $this->errors[] = "{$label}: skipped — Name is missing.";
            }
            return;
        }
        $label .= " ({$name})";

        $barcode = $text('barcode');
        $product = $this->findExisting($name, $barcode);

        $unitValue = $text('unit');
        if ($unitValue === '' && !$product) {
            $this->errors[] = "{$label}: skipped — Unit is missing.";
            return;
        }

        $values = [
            'item_code' => $text('item_code'),
            'description' => $text('description'),
            'brand' => $text('brand'),
            'barcode' => $barcode,
            'size' => $text('size'),
            'color' => $text('color'),
            'quantity' => $number('quantity'),
            'min_quantity' => $number('min_quantity'),
            'purchase_price' => $number('purchase_price'),
            'selling_price' => $number('selling_price'),
            'wholesale_price' => $number('wholesale_price'),
        ];

        if ($unitValue !== '') {
            $values['unit_id'] = $this->unit($unitValue, $label)->id;
        }

        $categoryValue = $text('category');
        if ($categoryValue !== '') {
            $category = $this->category($categoryValue, null, $label);
            $values['category_id'] = $category->id;

            $subcategoryValue = $text('subcategory');
            if ($subcategoryValue !== '') {
                $values['subcategory_id'] = $this->category($subcategoryValue, $category, $label)->id;
            }
        } elseif ($text('subcategory') !== '') {
            $this->errors[] = "{$label}: subcategory ignored — it needs a Category to belong to.";
        }

        $expire = $row['expire_date'] ?? null;
        if ($expire !== null && trim((string) $expire) !== '') {
            try {
                // Excel stores dates as serial numbers; text dates are parsed as written.
                $values['expire_date'] = is_numeric($expire)
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expire)->format('Y-m-d')
                    : \Carbon\Carbon::parse($expire)->format('Y-m-d');
            } catch (\Throwable $e) {
                $this->errors[] = "{$label}: expire date \"{$expire}\" is invalid and was ignored.";
            }
        }

        // Empty cells leave the product's current value alone.
        $values = array_filter($values, fn ($v) => $v !== null && $v !== '');

        if ($product) {
            $product->fill($values)->save();
            $this->updated++;
            return;
        }

        Products::create($values + [
            'name' => $name,
            'quantity' => 0,
            'min_quantity' => 0,
            'purchase_price' => 0,
            'selling_price' => 0,
            'wholesale_price' => 0,
            'admin_id' => Auth::id(),
            'shop_id' => $this->shopId,
            'sync_status' => 0,
        ]);
        $this->created++;
    }

    /** The product this row is about in the target shop: same barcode first, then same name. */
    protected function findExisting(string $name, string $barcode): ?Products
    {
        $inShop = fn () => Products::where('shop_id', $this->shopId);

        if ($barcode !== '' && ($product = $inShop()->where('barcode', $barcode)->oldest('id')->first())) {
            return $product;
        }

        return $inShop()->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->oldest('id')->first();
    }

    protected function unit(string $value, string $label): Unit
    {
        $unit = Unit::whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
            ->orWhereRaw('LOWER(short_name) = ?', [mb_strtolower($value)])
            ->first();

        if (!$unit) {
            $unit = Unit::create([
                'name' => $value,
                'short_name' => mb_substr($value, 0, 10),
                'shop_id' => $this->shopId,
                'admin_id' => Auth::id(),
            ]);
            $this->errors[] = "{$label}: unit \"{$value}\" didn't exist — added it to Units automatically.";
        }

        return $unit;
    }

    /** A top-level category, or (with $parent) a subcategory under it; created if missing. */
    protected function category(string $value, ?ProductCategory $parent, string $label): ProductCategory
    {
        $category = ProductCategory::whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
            ->where('parent_id', $parent?->id)
            ->first();

        if (!$category) {
            $category = ProductCategory::create([
                'name' => $value,
                'parent_id' => $parent?->id,
                'shop_id' => $this->shopId,
                'admin_id' => Auth::id(),
            ]);
            $this->errors[] = $parent
                ? "{$label}: subcategory \"{$value}\" didn't exist under \"{$parent->name}\" — added it automatically."
                : "{$label}: category \"{$value}\" didn't exist — added it automatically.";
        }

        return $category;
    }
}
