<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->enum('market_condition', [
                'trending',
                'ranging',
                'consolidating',
                'volatile',
                'choppy',
                'clean',
            ])
                ->nullable()
                ->after('timeframe');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn('market_condition');
        });
    }
};
