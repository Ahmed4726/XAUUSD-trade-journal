<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->foreignId('entry_type_id')
                ->nullable()
                ->after('trade_reason_id')
                ->constrained('entry_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropForeign([
                'entry_type_id',
            ]);

            $table->dropColumn('entry_type_id');
        });
    }
};
