<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_reason_id',
        'trade_mistake_id',

        'symbol',

        'entry_type_id',
        'trade_date',
        'trade_time',
        'weekday',

        'direction',
        'timeframe',
        'market_condition',

        'entry_price',
        'stop_loss',
        'take_profit',
        'exit_price',

        'lot_size',
        'contract_size',

        'risk_amount',
        'potential_profit',
        'risk_reward',
        'actual_profit_loss',

        'status',

        'notes',
        'why_entered',
        'expected_scenario',
        'confirmation_seen',
        'plan_vs_reality',
        'execution_rating',
        'what_went_well',
        'what_went_wrong',
        'lesson_learned',
    ];

    protected $casts = [
        'trade_date' => 'date',

        'entry_price' => 'decimal:2',
        'stop_loss' => 'decimal:2',
        'take_profit' => 'decimal:2',
        'exit_price' => 'decimal:2',

        'lot_size' => 'decimal:3',
        'contract_size' => 'decimal:2',

        'risk_amount' => 'decimal:2',
        'potential_profit' => 'decimal:2',
        'risk_reward' => 'decimal:2',
        'actual_profit_loss' => 'decimal:2',
        'execution_rating' => 'integer',
    ];

    public function reason(): BelongsTo
    {
        return $this->belongsTo(
            TradeReason::class,
            'trade_reason_id'
        );
    }

    public function mistake(): BelongsTo
    {
        return $this->belongsTo(
            TradeMistake::class,
            'trade_mistake_id'
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(TradeImage::class);
    }

    public function entryType(): BelongsTo
    {
        return $this->belongsTo(EntryType::class);
    }
}
