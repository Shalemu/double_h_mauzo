<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'admin_id',
        'user_id',

    ];

    // Shop belongs to an admin
    public function admin()
    {
        return $this->belongsTo(Users::class, 'admin_id');
    }

    // Shop can have assigned employee
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }


public function staff()
{
    return $this->hasMany(Staff::class, 'shop_id');
}

    // Optional: For future, shop can have many sales
    public function sales()
    {
        return $this->hasMany(Transaction::class);
    }
}
