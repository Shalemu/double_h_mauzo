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
 * A row updates the product that already exists in the target shop instead
 * of creating a copy, so importing the same file twice is safe. "Same
 * product" means same name plus the same barcode, or (without a barcode
 * match) the same name, brand, size and colour — so variants that share a
 * name (e.g. COCOA BUTTER 100ML and 400ML) stay separate products. Only
 * cells that are filled in overwrite what the product has; Quantity sets
 * the stock to that number.
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

    /** product id => first row number in this file that matched/created it */
    protected array $rowsByProduct = [];

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
        $product = $this->findExisting($name, $barcode, $text('brand'), $text('size'), $text('color'));

        if ($product && isset($this->rowsByProduct[$product->id])) {
            $this->errors[] = "{$label}: same product as Row {$this->rowsByProduct[$product->id]} (same name, barcode, brand, size and colour) — merged, this row's values were used.";
        }

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
            $this->rowsByProduct[$product->id] ??= $rowNumber;
            $this->updated++;
            return;
        }

        $product = Products::create($values + [
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
        $this->rowsByProduct[$product->id] = $rowNumber;
        $this->created++;
    }

    /**
     * The product this row is about in the target shop, or null to create one.
     *
     * 1. Same name and same barcode.
     * 2. Otherwise same name, brand, size and colour, where the product has
     *    no barcode or the same one (a different barcode = different product).
     */
    protected function findExisting(string $name, string $barcode, string $brand, string $size, string $color): ?Products
    {
        $norm = fn ($v) => mb_strtolower(trim((string) $v));

        $candidates = Products::where('shop_id', $this->shopId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$norm($name)])
            ->orderBy('id')
            ->get();

        if ($barcode !== '') {
            $byBarcode = $candidates->first(fn ($p) => $norm($p->barcode) === $norm($barcode));
            if ($byBarcode) {
                return $byBarcode;
            }
        }

        return $candidates->first(function ($p) use ($norm, $barcode, $brand, $size, $color) {
            $productBarcode = $norm($p->barcode);
            if ($barcode !== '' && $productBarcode !== '' && $productBarcode !== $norm($barcode)) {
                return false;
            }

            return $norm($p->brand) === $norm($brand)
                && $norm($p->size) === $norm($size)
                && $norm($p->color) === $norm($color);
        });
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
