<?php

declare(strict_types=1);

use App\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('order_statuses')->insert([
            'id' => OrderStatus::AWAITING_PAYMENT_ID,
            'name_en' => 'Awaiting Payment',
            'name_km' => 'រង់ចាំការទូទាត់ប្រាក់',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('order_statuses')
            ->where('id', OrderStatus::AWAITING_PAYMENT_ID)
            ->delete();
    }
};
