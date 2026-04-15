<?php

declare(strict_types=1);

namespace Database\Seeders;

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
        $firstName = env('DEFAULT_SUPER_ADMIN_FIRST_NAME', 'Super');
        $lastName = env('DEFAULT_SUPER_ADMIN_LAST_NAME', 'Admin');
        $phone = env('DEFAULT_SUPER_ADMIN_PHONE', '+85510000001');
        $password = env('DEFAULT_SUPER_ADMIN_PASSWORD', 'password123');

        $admin = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => $phone,
                'password' => Hash::make($password),
                'user_type_id' => UserType::ADMIN,
                'user_status_id' => UserStatus::ACTIVE,
            ]
        );

        $admin->adminProfile()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'department' => 'Platform Administration',
                'job_title' => 'Super Administrator',
                'office_phone' => $phone,
                'super_admin' => true,
                'permissions' => ['vendors.review', 'vendors.approve', 'users.manage', 'catalog.manage'],
            ]
        );
    }
}
