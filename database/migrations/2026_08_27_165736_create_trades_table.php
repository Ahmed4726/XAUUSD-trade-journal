<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('trade_reason_id')
                ->nullable()
                ->constrained('trade_reasons')
                ->nullOnDelete();

            $table->foreignId('trade_mistake_id')
                ->nullable()
                ->constrained('trade_mistakes')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Instrument
            |--------------------------------------------------------------------------
            */

            $table->string('symbol')->default('XAUUSD');

            /*
            |--------------------------------------------------------------------------
            | Trade Date / Time
            |--------------------------------------------------------------------------
            */

            $table->date('trade_date');
            $table->time('trade_time')->nullable();

            $table->string('weekday');

            /*
            |--------------------------------------------------------------------------
            | Trade Direction / Timeframe
            |--------------------------------------------------------------------------
            */

            $table->enum('direction', [
                'buy',
                'sell',
            ]);

            $table->enum('timeframe', [
                '15m',
                '30m',
                '1h',
                '4h',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Prices
            |--------------------------------------------------------------------------
            */

            $table->decimal('entry_price', 12, 2);

            $table->decimal('stop_loss', 12, 2);

            $table->decimal('take_profit', 12, 2);

            $table->decimal('exit_price', 12, 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Position
            |--------------------------------------------------------------------------
            */

            $table->decimal('lot_size', 10, 3);

            $table->decimal('contract_size', 10, 2)->default(100);

            /*
            |--------------------------------------------------------------------------
            | Calculated Values
            |--------------------------------------------------------------------------
            */

            $table->decimal('risk_amount', 12, 2)->nullable();

            $table->decimal('potential_profit', 12, 2)->nullable();

            $table->decimal('risk_reward', 10, 2)->nullable();

            $table->decimal('actual_profit_loss', 12, 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'open',
                'win',
                'loss',
                'breakeven',
            ])->default('open');

            /*
            |--------------------------------------------------------------------------
            | Journal
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->text('what_went_well')->nullable();

            $table->text('what_went_wrong')->nullable();

            $table->text('lesson_learned')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('trade_date');
            $table->index('direction');
            $table->index('timeframe');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
