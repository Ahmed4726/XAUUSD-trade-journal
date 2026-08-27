<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trade_images', function (Blueprint $table) {
            $table->string('type')->default('before')->change();
            $table->string('original_name')->nullable()->after('path');
        });

        DB::table('trade_images')->where('type', 'other')->update(['type' => 'entry']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('trade_images')->where('type', 'entry')->update(['type' => 'other']);

        Schema::table('trade_images', function (Blueprint $table) {
            $table->dropColumn('original_name');
            $table->enum('type', ['before', 'after', 'other'])->default('before')->change();
        });
    }
};
