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
        Schema::table('trades', function (Blueprint $table) {
            $table->text('why_entered')->nullable()->after('notes');
            $table->text('expected_scenario')->nullable()->after('why_entered');
            $table->text('confirmation_seen')->nullable()->after('expected_scenario');
            $table->text('plan_vs_reality')->nullable()->after('confirmation_seen');
            $table->unsignedTinyInteger('execution_rating')->nullable()->after('plan_vs_reality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn([
                'why_entered',
                'expected_scenario',
                'confirmation_seen',
                'plan_vs_reality',
                'execution_rating',
            ]);
        });
    }
};
