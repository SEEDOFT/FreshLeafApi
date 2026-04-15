<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasTable('admins')) {
            $admins = DB::table('admins')->get();

            foreach ($admins as $admin) {
                $existingUserQuery = DB::table('users')
                    ->where('phone_number', $admin->phone_number);

                if ($admin->email !== null) {
                    $existingUserQuery->orWhere('email', $admin->email);
                }

                $existingUser = $existingUserQuery->first();

                $userId = $existingUser?->id;

                if ($userId === null) {
                    $userId = DB::table('users')->insertGetId([
                        'first_name' => (string) $admin->first_name,
                        'last_name' => (string) $admin->last_name,
                        'email' => $admin->email,
                        'phone_number' => (string) $admin->phone_number,
                        'password' => (string) $admin->password,
                        'user_type_id' => 3,
                        'user_status_id' => ((int) $admin->admin_status_id === 1) ? 1 : 2,
                        'created_at' => $admin->created_at,
                        'updated_at' => $admin->updated_at,
                    ]);
                } else {
                    DB::table('users')->where('id', $userId)->update([
                        'user_type_id' => 3,
                        'user_status_id' => ((int) $admin->admin_status_id === 1) ? 1 : 2,
                        'updated_at' => now(),
                    ]);
                }

                if (Schema::hasTable('admin_profiles')) {
                    DB::table('admin_profiles')->updateOrInsert(
                        ['user_id' => $userId],
                        [
                            'department' => $admin->department,
                            'job_title' => $admin->job_title,
                            'office_phone' => $admin->office_phone,
                            'super_admin' => false,
                            'permissions' => $admin->permissions,
                            'updated_at' => now(),
                            'created_at' => $admin->created_at ?? now(),
                        ]
                    );
                }
            }
        }

        if (Schema::hasTable('vendors')) {
            $vendors = DB::table('vendors')->get();

            foreach ($vendors as $vendor) {
                $existingUserQuery = DB::table('users')
                    ->where('phone_number', $vendor->phone_number);

                if ($vendor->email !== null) {
                    $existingUserQuery->orWhere('email', $vendor->email);
                }

                $existingUser = $existingUserQuery->first();

                $userId = $existingUser?->id;

                if ($userId === null) {
                    $userId = DB::table('users')->insertGetId([
                        'first_name' => (string) $vendor->first_name,
                        'last_name' => (string) $vendor->last_name,
                        'email' => $vendor->email,
                        'phone_number' => (string) $vendor->phone_number,
                        'password' => (string) $vendor->password,
                        'user_type_id' => 2,
                        'user_status_id' => ((int) $vendor->vendor_status_id === 1) ? 1 : (((int) $vendor->vendor_status_id === 3) ? 4 : 2),
                        'created_at' => $vendor->created_at,
                        'updated_at' => $vendor->updated_at,
                    ]);
                } else {
                    DB::table('users')->where('id', $userId)->update([
                        'user_type_id' => 2,
                        'user_status_id' => ((int) $vendor->vendor_status_id === 1) ? 1 : (((int) $vendor->vendor_status_id === 3) ? 4 : 2),
                        'updated_at' => now(),
                    ]);
                }

                if (Schema::hasTable('vendor_profiles')) {
                    DB::table('vendor_profiles')->updateOrInsert(
                        ['user_id' => $userId],
                        [
                            'business_name' => $vendor->business_name,
                            'contact_phone' => $vendor->phone_number,
                            'city' => $vendor->city,
                            'province' => $vendor->province,
                            'address' => $vendor->address,
                            'is_verified' => (bool) $vendor->is_verified,
                            'meta' => $vendor->meta,
                            'updated_at' => now(),
                            'created_at' => $vendor->created_at ?? now(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank because this migration performs data synchronization.
    }
};
