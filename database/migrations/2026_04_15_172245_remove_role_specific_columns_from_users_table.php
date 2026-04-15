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
        $hasPinColumn = Schema::hasColumn('users', 'pin');
        $hasPreferredLanguageColumn = Schema::hasColumn('users', 'preferred_language');

        if (! $hasPinColumn && ! $hasPreferredLanguageColumn) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($hasPinColumn, $hasPreferredLanguageColumn): void {
            if ($hasPinColumn) {
                $table->dropColumn('pin');
            }

            if ($hasPreferredLanguageColumn) {
                $table->dropColumn('preferred_language');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasPinColumn = Schema::hasColumn('users', 'pin');
        $hasPreferredLanguageColumn = Schema::hasColumn('users', 'preferred_language');

        if ($hasPinColumn && $hasPreferredLanguageColumn) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($hasPinColumn, $hasPreferredLanguageColumn): void {
            if (! $hasPinColumn) {
                $table->string('pin')->nullable()->after('image');
            }

            if (! $hasPreferredLanguageColumn) {
                $table->string('preferred_language')->default('en')->after('pin');
            }
        });
    }
};
