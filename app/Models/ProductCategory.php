<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;

protected $fillable = [
    'name',
    'description',  
    'parent_id',
    'shop_id',
    'admin_id',
];


    // Parent category
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    // Subcategories
    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }
}
