<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The signed-in person as the mobile app sees them.
 *
 * Only the fields listed here leave the server — never the password,
 * wages or internal columns. The app uses:
 *   - type         'staff' or 'admin', to pick which screens to show
 *   - permissions  to hide buttons the person may not use (the server
 *                  still checks every request; this is only for the UI)
 *   - shop         the staff member's shop (null for admins)
 *
 * @mixin \App\Models\Users
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->isStaff() ? 'staff' : 'admin',
            'full_name'        => trim($this->first_name . ' ' . $this->last_name),
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'phone'       => $this->phone,
            'email'       => $this->email,
            'role'        => $this->role ? ['id' => $this->role->id, 'name' => $this->role->name] : null,
            // '*' = full access (super admin / admin role).
            'permissions' => $this->hasFullAccess() ? ['*'] : ($this->role?->permissionKeys() ?? []),
            'shop'        => $this->isStaff() && $this->shop
                ? ['id' => $this->shop->id, 'name' => $this->shop->name, 'location' => $this->shop->location]
                : null,
        ];
    }
}
