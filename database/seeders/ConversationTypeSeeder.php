<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ConversationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            ConversationType::insert([
                ['id' => 1, 'name' => 'direct', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'support', 'created_at' => now(), 'updated_at' => now()],
            ]);
        });
    }
}
