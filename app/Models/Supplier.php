<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
  

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'shop_id', // if you want suppliers linked to shops
        'description'
    ];

    // If suppliers are linked to shops
    public function shop()
    {
        return $this->belongsTo(Shops::class, 'shop_id');
    }

    // A supplier can have many purchases
    public function purchases()
    {
        return $this->hasMany(Purchases::class);
    }
}
