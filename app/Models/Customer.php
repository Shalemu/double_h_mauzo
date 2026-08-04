<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'shop_id'
    ];

        public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class);
    }

    public function shop()
    {
        return $this->belongsTo(\App\Models\Shops::class, 'shop_id');
    }
}
