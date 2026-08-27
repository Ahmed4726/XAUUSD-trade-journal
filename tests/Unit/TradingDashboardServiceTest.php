<?php

namespace Tests\Unit;

use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeMistake;
use App\Models\TradeReason;
use App\Services\TradingDashboardService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TradingDashboardServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_calculates_kpis_breakdowns_and_cumulative_profit_loss(): void
    {
        $setup = TradeReason::create(['name' => 'Clean S/R', 'is_active' => true]);
        $entryType = EntryType::create(['name' => 'Dynamic', 'is_active' => true]);
        $mistake = TradeMistake::create(['name' => 'FOMO', 'is_active' => true]);

        $this->createTrade($setup, $entryType, ['trade_date' => '2026-08-03', 'trade_time' => '09:00', 'weekday' => 'Monday', 'direction' => 'buy', 'timeframe' => '15m', 'status' => 'win', 'actual_profit_loss' => 100, 'risk_reward' => 2]);
        $this->createTrade($setup, $entryType, ['trade_date' => '2026-08-03', 'trade_time' => '10:00', 'weekday' => 'Monday', 'direction' => 'sell', 'timeframe' => '1h', 'status' => 'loss', 'actual_profit_loss' => -40, 'risk_reward' => 1, 'trade_mistake_id' => $mistake->id]);
        $this->createTrade($setup, $entryType, ['trade_date' => '2026-08-04', 'trade_time' => '09:00', 'weekday' => 'Tuesday', 'direction' => 'buy', 'timeframe' => '15m', 'status' => 'open', 'actual_profit_loss' => null, 'risk_reward' => 3]);

        $analytics = (new TradingDashboardService)->build([]);

        $this->assertSame(3, $analytics['kpis']['trades']);
        $this->assertSame(1, $analytics['kpis']['wins']);
        $this->assertSame(1, $analytics['kpis']['losses']);
        $this->assertSame(50.0, $analytics['kpis']['win_rate']);
        $this->assertSame(60.0, $analytics['kpis']['net_profit_loss']);
        $this->assertSame(1.5, $analytics['kpis']['average_risk_reward']);
        $this->assertSame(2.5, $analytics['kpis']['profit_factor']);
        $this->assertSame(2, $analytics['timeframes']->firstWhere('key', '15m')['trades']);
        $this->assertSame(3, $analytics['setups']->firstWhere('name', 'Clean S/R')['trades']);
        $this->assertSame(3, $analytics['entryTypes']->firstWhere('name', 'Dynamic')['trades']);
        $this->assertSame(3, $analytics['conditions']->firstWhere('key', 'trending')['trades']);
        $this->assertSame(2, $analytics['weekdays']->firstWhere('key', 'Monday')['trades']);
        $this->assertSame(100.0, $analytics['directions']->firstWhere('key', 'buy')['net_profit_loss']);
        $this->assertSame(1, $analytics['mistakes']->firstWhere('name', 'FOMO')['trades']);
        $this->assertSame(2, $analytics['daily']->first()['trades']);
        $this->assertSame(60.0, $analytics['equityCurve']->last()['cumulative_profit_loss']);
    }

    public function test_filters_combine_and_date_range_limits_analytics(): void
    {
        $setup = TradeReason::create(['name' => 'Breakout', 'is_active' => true]);
        $entryType = EntryType::create(['name' => 'Confirmation', 'is_active' => true]);
        $this->createTrade($setup, $entryType, ['trade_date' => '2026-08-10', 'direction' => 'buy', 'timeframe' => '15m', 'status' => 'win', 'actual_profit_loss' => 50]);
        $this->createTrade($setup, $entryType, ['trade_date' => '2026-08-11', 'direction' => 'sell', 'timeframe' => '15m', 'status' => 'win', 'actual_profit_loss' => 90]);

        $analytics = (new TradingDashboardService)->build(['period' => 'custom', 'date_from' => '2026-08-10', 'date_to' => '2026-08-10', 'direction' => 'buy', 'timeframe' => '15m']);

        $this->assertSame(1, $analytics['kpis']['trades']);
        $this->assertSame(50.0, $analytics['kpis']['net_profit_loss']);
    }

    public function test_empty_data_and_zero_gross_loss_do_not_return_invalid_metrics(): void
    {
        $empty = (new TradingDashboardService)->build([]);
        $this->assertSame(0, $empty['kpis']['trades']);
        $this->assertNull($empty['kpis']['win_rate']);
        $this->assertNull($empty['kpis']['profit_factor']);

        $setup = TradeReason::create(['name' => 'Retest', 'is_active' => true]);
        $entryType = EntryType::create(['name' => 'Immediate', 'is_active' => true]);
        $this->createTrade($setup, $entryType, ['status' => 'win', 'actual_profit_loss' => 40]);

        $analytics = (new TradingDashboardService)->build([]);
        $this->assertNull($analytics['kpis']['profit_factor']);
    }

    private function createTrade(TradeReason $setup, EntryType $entryType, array $overrides = []): Trade
    {
        return Trade::create(array_merge([
            'trade_reason_id' => $setup->id,
            'entry_type_id' => $entryType->id,
            'symbol' => 'XAUUSD',
            'trade_date' => '2026-08-01',
            'trade_time' => '08:00',
            'weekday' => 'Friday',
            'direction' => 'buy',
            'timeframe' => '1h',
            'market_condition' => 'trending',
            'entry_price' => 3350,
            'stop_loss' => 3345,
            'take_profit' => 3360,
            'lot_size' => 0.10,
            'contract_size' => 100,
            'risk_amount' => 50,
            'potential_profit' => 100,
            'risk_reward' => 2,
            'status' => 'open',
            'actual_profit_loss' => null,
        ], $overrides));
    }
}
