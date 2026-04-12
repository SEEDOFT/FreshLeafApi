<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->string('remember_token', 191)->nullable();
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('status_id');
            $table->string('business_name', 160);
            $table->string('contact_phone', 40)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('province', 120)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('meta')->nullable();
            $table->index('status_id');
            $table->index('type_id');
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->string('remember_token', 191)->nullable();
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('status_id');
            $table->string('department', 120)->nullable();
            $table->string('job_title', 120)->nullable();
            $table->string('office_phone', 40)->nullable();
            $table->boolean('super_admin')->default(false);
            $table->json('permissions')->nullable();
            $table->index('status_id');
            $table->index('type_id');
            $table->timestamps();
        });

        $vendorTypeId = DB::table('user_types')->where('name', 'Operation')->value('id')
            ?? DB::table('user_types')->where('name', 'Vendor')->value('id')
            ?? 2;

        $adminTypeId = DB::table('user_types')->where('name', 'Admin')->value('id') ?? 3;

        $vendorStatusId = DB::table('user_statuses')->where('name', 'Active')->value('id') ?? 1;
        $adminStatusId = DB::table('user_statuses')->where('name', 'Active')->value('id') ?? 1;

        DB::table('users')
            ->where('user_type_id', $vendorTypeId)
            ->orderBy('id')
            ->chunk(200, function ($users) use ($vendorTypeId, $vendorStatusId): void {
                $vendorProfiles = collect();

                if (Schema::hasTable('vendor_profiles')) {
                    $userIds = $users->pluck('id')->all();
                    $vendorProfiles = DB::table('vendor_profiles')
                        ->whereIn('user_id', $userIds)
                        ->get()
                        ->keyBy('user_id');
                }

                foreach ($users as $user) {
                    $profile = $vendorProfiles->get($user->id);
                    $name = trim($user->first_name.' '.$user->last_name);
                    $email = $user->email;

                    DB::table('vendors')->updateOrInsert(
                        ['email' => $email],
                        [
                            'name' => $name,
                            'password' => $user->password,
                            'type_id' => $vendorTypeId,
                            'status_id' => $user->user_status_id ?: $vendorStatusId,
                            'business_name' => $profile?->business_name ?? $name.' Vendor',
                            'contact_phone' => $profile?->contact_phone ?? $user->phone_number,
                            'city' => $profile?->city,
                            'province' => $profile?->province,
                            'address' => $profile?->address,
                            'is_verified' => (bool) ($profile?->is_verified ?? false),
                            'meta' => $profile?->meta,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            });

        DB::table('users')
            ->where('user_type_id', $adminTypeId)
            ->orderBy('id')
            ->chunk(200, function ($users) use ($adminTypeId, $adminStatusId): void {
                $adminProfiles = collect();

                if (Schema::hasTable('admin_profiles')) {
                    $userIds = $users->pluck('id')->all();
                    $adminProfiles = DB::table('admin_profiles')
                        ->whereIn('user_id', $userIds)
                        ->get()
                        ->keyBy('user_id');
                }

                foreach ($users as $user) {
                    $profile = $adminProfiles->get($user->id);
                    $name = trim($user->first_name.' '.$user->last_name);
                    $email = $user->email;

                    DB::table('admins')->updateOrInsert(
                        ['email' => $email],
                        [
                            'name' => $name,
                            'password' => $user->password,
                            'type_id' => $adminTypeId,
                            'status_id' => $user->user_status_id ?: $adminStatusId,
                            'department' => $profile?->department,
                            'job_title' => $profile?->job_title,
                            'office_phone' => $profile?->office_phone ?? $user->phone_number,
                            'super_admin' => (bool) ($profile?->super_admin ?? false),
                            'permissions' => $profile?->permissions,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('vendors');
    }
};
