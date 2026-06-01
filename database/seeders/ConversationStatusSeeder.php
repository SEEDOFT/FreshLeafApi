<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ConversationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            ConversationStatus::insert([
                ['id' => 1, 'name' => 'open', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'closed', 'created_at' => now(), 'updated_at' => now()],
            ]);
        });
    }
}
