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
        Schema::table('vendor_profiles', static function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('is_verified');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->unsignedBigInteger('approved_by_admin_id')->nullable()->after('rejected_at');
            $table->unsignedBigInteger('rejected_by_admin_id')->nullable()->after('approved_by_admin_id');
            $table->text('approve_reason')->nullable()->after('rejected_by_admin_id');
            $table->text('reject_reason')->nullable()->after('approve_reason');

            $table->index('approved_by_admin_id');
            $table->index('rejected_by_admin_id');

            $table->foreign('approved_by_admin_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('rejected_by_admin_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_profiles', static function (Blueprint $table) {
            $table->dropForeign(['approved_by_admin_id']);
            $table->dropForeign(['rejected_by_admin_id']);
            $table->dropIndex(['approved_by_admin_id']);
            $table->dropIndex(['rejected_by_admin_id']);

            $table->dropColumn([
                'approved_at',
                'rejected_at',
                'approved_by_admin_id',
                'rejected_by_admin_id',
                'approve_reason',
                'reject_reason',
            ]);
        });
    }
};
