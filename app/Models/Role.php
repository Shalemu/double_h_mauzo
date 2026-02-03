<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Allow mass assignment
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Staff that belong to this role
     */
    public function staff()
    {
        return $this->hasMany(Staff::class, 'role_id');
    }

    /**
     * System users (admins, etc.)
     * (Only keep this if you really have a users table with role_id)
     */
    public function users()
    {
        return $this->hasMany(Users::class, 'role_id');
    }
}
