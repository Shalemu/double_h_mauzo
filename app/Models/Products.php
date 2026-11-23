<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'item_code',
        'barcode',
        'barcode_symbology',
        'description',
        'discount_type',
        'discount_value',
        'product_type',
        'brand',
        'category_id',
        'subcategory_id',
        'unit_id',
        'min_quantity',
        'quantity',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'invoice_number',
        'expire_date',
        'size',
        'color',
        'image',
        'store_id',
        'shop_id',
        'admin_id',
        'selling_type',
        'sync_status',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ProductCategory::class, 'subcategory_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function purchases()
{
    return $this->hasMany(Purchases::class);
}

    public function shop()
    {
        return $this->belongsTo(Shops::class, 'shop_id');
    }

    /**
     * POS product search, shared by the web POS and the mobile API so both
     * find the same products: name, brand, barcode, "brand name", or id.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', '%' . $term . '%')
              ->orWhere('brand', 'like', '%' . $term . '%')
              ->orWhere('barcode', 'like', '%' . $term . '%')
              ->orWhereRaw("CONCAT_WS(' ', brand, name) LIKE ?", ['%' . $term . '%']);
            if (ctype_digit($term)) {
                $q->orWhere('id', (int) $term);
            }
        });
    }

    /**
     * A barcode belongs to one product per shop (scanning it must find exactly
     * one). The same barcode in another shop is fine: that's the same product's
     * copy there, and stock transfers match on it.
     *
     * @return static|null the product in $shopId that already has this barcode
     */
    public static function barcodeOwner($shopId, ?string $barcode, $ignoreId = null): ?self
    {
        $barcode = trim((string) $barcode);
        if ($barcode === '' || !$shopId) {
            return null;
        }

        return static::where('shop_id', $shopId)
            ->where('barcode', $barcode)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->first(['id', 'name', 'barcode']);
    }

    /** Throws a validation error on `$field` when the barcode is taken in this shop. */
    public static function ensureBarcodeFree($shopId, ?string $barcode, $ignoreId = null, string $field = 'barcode'): void
    {
        if ($owner = static::barcodeOwner($shopId, $barcode, $ignoreId)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => "Barcode {$owner->barcode} is already used by \"{$owner->name}\" in this shop. Each product needs its own barcode.",
            ]);
        }
    }

    protected static function booted()
    {
        // Typing a new brand on a product adds it to the Brands list.
        static::saved(function (Products $product) {
            $brand = trim((string) $product->brand);
            if ($brand !== '' && ($product->wasRecentlyCreated || $product->wasChanged('brand'))) {
                Brand::firstOrCreate(['name' => $brand]);
            }
        });
    }

}
