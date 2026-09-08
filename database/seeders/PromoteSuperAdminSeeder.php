<?php

namespace Database\Seeders;

use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PromoteSuperAdminSeeder extends Seeder
{
    /**
     * Promote the existing shadrackmussa97@gmail.com account to Super Admin.
     */
    public function run(): void
    {
        $user = Users::where('email', 'shadrackmussa97@gmail.com')->first();

        if (!$user) {
            $this->command->warn('shadrackmussa97@gmail.com not found — nothing to promote.');
            return;
        }

        $user->super_user = true;
        $user->role_id = $user->role_id > 0 ? $user->role_id : 1;
        $user->verified = true;
        $user->verified_at = $user->verified_at ?? now();
        $user->password = Hash::make('Superadmin@123');
        $user->save();

        $this->command->info('shadrackmussa97@gmail.com promoted to Super Admin.');
    }
}
