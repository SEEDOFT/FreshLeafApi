<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConversationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ConversationStatus::insert([
            ['id' => 1, 'name' => 'open', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'closed', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
