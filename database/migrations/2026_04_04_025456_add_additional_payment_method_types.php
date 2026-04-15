<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('payment_method_types')->insert([
            ['id' => 4, 'code' => 'american_express', 'name' => 'American Express', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'code' => 'discover', 'name' => 'Discover', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'code' => 'jcb', 'name' => 'JCB', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'code' => 'diners_club', 'name' => 'Diners Club', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'code' => 'paypal', 'name' => 'PayPal', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'code' => 'stripe', 'name' => 'Stripe', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_method_types')->whereIn('id', [4, 5, 6, 7, 8, 9])->delete();
    }
};
