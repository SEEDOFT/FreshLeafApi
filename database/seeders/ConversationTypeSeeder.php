<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConversationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ConversationType::insert([
            ['id' => 1, 'name' => 'direct', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'support', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
