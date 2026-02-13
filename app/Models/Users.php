<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Role; 

class Users extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'verified',
        'verified_at',
        'role_id',
        'super_user',
        'user_type',
        'login_trials',
        'password_reset',
        'code',
        'expires_at',
        'admin_id',
        'sync_status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'verified' => 'boolean',
        'super_user' => 'boolean',
        'password_reset' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    //  ADD THIS
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

      public function shop()
    {
        return $this->hasOne(\App\Models\Shops::class, 'admin_id');
    }

    public function expenses()
{
    return $this->morphMany(Expenses::class, 'created_by');
}

}
