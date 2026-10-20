<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Brand list for picking/renaming. Products keep the brand as text in
 * products.brand, matched by name.
 */
class Brand extends Model
{
    protected $fillable = ['name', 'description'];

    public function products()
    {
        return $this->hasMany(Products::class, 'brand', 'name');
    }
}
