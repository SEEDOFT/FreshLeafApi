<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('user_type_id')->nullable()->after('password')->constrained('user_types')->restrictOnDelete();
            $table->foreignId('status_id')->nullable()->after('user_type_id')->constrained('user_statuses')->restrictOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropForeign(['status_id']);
            $table->dropColumn(['phone', 'user_type_id', 'status_id', 'deleted_at']);
        });
    }
};
