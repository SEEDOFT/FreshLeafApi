<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('DEFAULT_SUPER_ADMIN_EMAIL', 'superadmin@freshleaf.local');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'first_name' => env('DEFAULT_SUPER_ADMIN_FIRST_NAME', 'Super'),
                'last_name' => env('DEFAULT_SUPER_ADMIN_LAST_NAME', 'Admin'),
                'phone_number' => env('DEFAULT_SUPER_ADMIN_PHONE', '+85510000001'),
                'password' => Hash::make(env('DEFAULT_SUPER_ADMIN_PASSWORD', 'password123')),
                'user_type_id' => UserType::ADMIN,
                'user_status_id' => UserStatus::ACTIVE,
            ]
        );

        AdminProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'department' => 'Platform Administration',
                'job_title' => 'Super Administrator',
                'office_phone' => env('DEFAULT_SUPER_ADMIN_PHONE', '+85510000001'),
                'super_admin' => true,
                'permissions' => ['vendors.review', 'vendors.approve', 'users.manage', 'catalog.manage'],
            ]
        );
    }
}
