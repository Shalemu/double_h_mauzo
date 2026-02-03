<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'shop_id',
        'role_id', 
        'wages',
        'password',
    ];

    protected $hidden = ['password'];

       public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
