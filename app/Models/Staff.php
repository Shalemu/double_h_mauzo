<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;

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

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    // Accessor
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Use phone as the auth identifier
    public function getAuthIdentifierName()
    {
        return 'phone';
    }

    public function expenses()
{
    return $this->morphMany(Expenses::class, 'created_by');
}

}
