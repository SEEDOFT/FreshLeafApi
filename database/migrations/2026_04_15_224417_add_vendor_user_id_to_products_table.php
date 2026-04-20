<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'vendor_user_id')) {
            Schema::table('products', static function (Blueprint $table) {
                $table->foreignId('vendor_user_id')
                    ->nullable()
                    ->after('product_status_id')
                    ->constrained('users', 'id')
                    ->cascadeOnUpdate()
                    ->nullOnDelete()
                    ->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'vendor_user_id')) {
            Schema::table('products', static function (Blueprint $table) {
                $table->dropConstrainedForeignId('vendor_user_id');
            });
        }
    }
};
