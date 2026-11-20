<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * A shop staff member: a row in `users` with user_type = 'staff'.
 *
 * Staff::query() only ever sees staff rows, and Users queries hand staff rows
 * back as this class (see Users::newFromBuilder), so auth()->user() for a
 * staff member is a Staff.
 */
class Staff extends Users
{
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

    protected $appends = ['full_name'];

    protected static function booted(): void
    {
        parent::booted(); // shared Users behaviour (e.g. revoking API tokens on delete)

        static::addGlobalScope('staff', fn (Builder $q) => $q->where('users.user_type', self::TYPE_STAFF));

        static::creating(function (Staff $staff) {
            $staff->user_type = self::TYPE_STAFF;
            $staff->verified = true;
            $staff->username ??= self::freeUsername($staff->phone);
        });

        static::saving(function (Staff $staff) {
            $staff->name = trim($staff->first_name . ' ' . $staff->last_name);
            $staff->email = $staff->email ?: null;
        });
    }

    /**
     * The logged-in staff member, or null when nobody — or an admin — is
     * logged in. Replaces the old Auth::guard('staff')->user().
     */
    public static function current(): ?self
    {
        $user = auth()->user();

        return $user instanceof self ? $user : null;
    }

    /**
     * Staff and admins share one morph type, so notifications and expenses
     * created as a Staff are found again through auth()->user().
     */
    public function getMorphClass()
    {
        return Users::class;
    }

    // Relationships
    public function shop()
    {
        return $this->belongsTo(Shops::class, 'shop_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'staff_id');
    }

    // Staff only ever get what their role grants — never the admin panel.
    public function hasPermission(string $permission): bool
    {
        return $this->role && in_array($permission, $this->role->permissionKeys(), true);
    }

    public function isAdminTier(): bool
    {
        return false;
    }

    public function hasFullAccess(): bool
    {
        return false;
    }

    public function hasAnyPanelPermission(): bool
    {
        return false;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    private static function freeUsername(?string $phone): string
    {
        foreach ([$phone, 'staff_' . $phone] as $candidate) {
            if ($candidate && !Users::withTrashed()->where('username', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'staff_' . $phone . '_' . uniqid();
    }
}
