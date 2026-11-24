<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Role; 
use Laravel\Sanctum\HasApiTokens;

/**
 * Everyone who can log in: admins and shop staff (user_type = 'staff').
 *
 * Rows with user_type 'staff' are hydrated as App\Models\Staff (single-table
 * inheritance), so auth()->user() for a staff member is a Staff instance with
 * the staff rules (its own shop, POS-only permissions).
 */
class Users extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    public const TYPE_STAFF = 'staff';

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'profile_image',
        'username',
        'email',
        'phone',
        'password',
        'verified',
        'verified_at',
        'role_id',
        'shop_id',
        'wages',
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

    protected static function booted(): void
    {
        // A removed person must not keep using the mobile app: revoke all
        // their API tokens (Staff calls parent::booted() so this applies to staff too).
        static::deleted(fn (Users $user) => $user->tokens()->delete());
    }

    /**
     * Hydrate staff rows as Staff so every query on users (auth included)
     * hands back the right class.
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attributes = (array) $attributes;

        if (static::class === self::class && ($attributes['user_type'] ?? null) === self::TYPE_STAFF) {
            $model = (new Staff)->newInstance([], true);
            $model->setRawAttributes($attributes, true);
            $model->setConnection($connection ?: $this->getConnectionName());
            $model->fireModelEvent('retrieved', false);

            return $model;
        }

        return parent::newFromBuilder($attributes, $connection);
    }

    public function isStaff(): bool
    {
        return $this->user_type === self::TYPE_STAFF;
    }

    /** Admin-panel accounts only (everyone except shop staff). */
    public function scopeNotStaff($query)
    {
        return $query->where($this->qualifyColumn('user_type'), '!=', self::TYPE_STAFF);
    }

    /**
     * {user} in admin user-management routes must never resolve to a staff
     * member — staff are managed on the Staff page.
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $query = parent::resolveRouteBindingQuery($query, $value, $field);

        return static::class === self::class ? $query->notStaff() : $query;
    }

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

    /**
     * Admin-tier accounts (role_id 1 "Super Admin", role_id 2 "Admin", or
     * the super_user flag) aren't tied to a single owned shop — they can
     * see and manage every shop.
     */
    public function isAdminTier(): bool
    {
        return in_array($this->role_id, [1, 2]) || (bool) $this->super_user || $this->hasAnyPanelPermission();
    }

    /**
     * The "admin" role (id 1) and super users can do everything; every other
     * admin-panel role only what's ticked on Roles & Permissions.
     */
    public function hasFullAccess(): bool
    {
        return (int) $this->role_id === 1 || (bool) $this->super_user;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->hasFullAccess()
            || ($this->role && in_array($permission, $this->role->permissionKeys(), true));
    }

    /**
     * Any admin-panel permission at all — what lets a custom role into the
     * admin panel (staff "POS …" permissions don't count).
     */
    public function hasAnyPanelPermission(): bool
    {
        if (!$this->role) {
            return false;
        }

        $staffOnly = collect(config('permissions'))
            ->filter(fn ($perms, $module) => str_starts_with($module, 'POS '))
            ->flatMap(fn ($perms) => array_keys($perms))
            ->all();

        return (bool) array_diff($this->role->permissionKeys(), $staffOnly);
    }

    public function expenses()
{
    return $this->morphMany(Expenses::class, 'created_by');
}

}
