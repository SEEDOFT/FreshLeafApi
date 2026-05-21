<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_inventories', function (Blueprint $table) {
            $table->dropColumn('packaging_type');
            $table->foreignId('packaging_type_id')->nullable()->after('certification_type')->constrained('packaging_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_inventories', function (Blueprint $table) {
            $table->dropForeign(['packaging_type_id']);
            $table->dropColumn('packaging_type_id');
            $table->string('packaging_type')->nullable();
        });
    }
};
