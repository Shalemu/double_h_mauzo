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

}
