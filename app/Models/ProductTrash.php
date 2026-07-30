<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTrash extends Model
{
    use HasFactory;

    protected $table = 'product_trash';

    protected $fillable = [
        'original_id',
        'shop_id',
        'name',
        'brand',
        'category_id',
        'unit_id',
        'quantity',
        'min_quantity',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'invoice_number',
        'barcode',
        'expire_date',
        'size',
        'color',
        'image',
        'admin_id'
    ];

        public function category()
    {
        return $this->belongsTo(\App\Models\ProductCategory::class, 'category_id');
    }

    // Subcategory relationship
    public function subcategory()
    {
        return $this->belongsTo(\App\Models\ProductCategory::class, 'subcategory_id');
    }

    // Unit relationship
    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'unit_id');
    }
}