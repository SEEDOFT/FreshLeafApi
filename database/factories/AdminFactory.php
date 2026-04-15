<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\AdminStatus;
use App\Models\AdminType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'email' => Str::lower(Str::slug($name)).'@admin.local',
            'password' => Hash::make('password'),
            'type_id' => AdminType::SUPER_ADMIN,
            'status_id' => AdminStatus::ACTIVE,
            'department' => fake()->randomElement(['Operations', 'Finance', 'Catalog', 'Support']),
            'job_title' => fake()->jobTitle(),
            'office_phone' => fake()->phoneNumber(),
            'super_admin' => false,
            'permissions' => ['catalog.manage'],
        ];
    }
}
