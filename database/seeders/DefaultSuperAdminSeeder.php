<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AdminStatus;
use App\Models\AdminType;
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
        $name = env('DEFAULT_SUPER_ADMIN_FIRST_NAME', 'Super').' '.env('DEFAULT_SUPER_ADMIN_LAST_NAME', 'Admin');
        $phone = env('DEFAULT_SUPER_ADMIN_PHONE', '+85510000001');
        $password = env('DEFAULT_SUPER_ADMIN_PASSWORD', 'password123');

        Admin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'type_id' => AdminType::SUPER_ADMIN,
                'status_id' => AdminStatus::ACTIVE,
                'department' => 'Platform Administration',
                'job_title' => 'Super Administrator',
                'office_phone' => $phone,
                'super_admin' => true,
                'permissions' => ['vendors.review', 'vendors.approve', 'users.manage', 'catalog.manage'],
            ]
        );
    }
}
