<?php

namespace Tests\Feature;

use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeReason;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_is_the_landing_page_and_renders_analytics(): void
    {
        $setup = TradeReason::create(['name' => 'Breakout', 'is_active' => true]);
        $entryType = EntryType::create(['name' => 'Confirmation', 'is_active' => true]);
        Trade::create([
            'trade_reason_id' => $setup->id,
            'entry_type_id' => $entryType->id,
            'symbol' => 'XAUUSD',
            'trade_date' => '2026-08-28',
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
            'status' => 'win',
            'actual_profit_loss' => 50,
        ]);

        $response = $this->get(route('dashboard', ['period' => 'custom', 'date_from' => '2026-08-28', 'date_to' => '2026-08-28', 'direction' => 'buy', 'timeframe' => '1h']));

        $response->assertOk();
        $response->assertSee('Trading dashboard');
        $response->assertSee('Performance by price action setup');
        $response->assertSee('value="buy" selected', false);
        $response->assertSee('value="2026-08-28"', false);
        $response->assertSee('Date: Aug 28, 2026 – Aug 28, 2026');
    }

    public function test_dashboard_shows_empty_state_when_no_trades_match_filters(): void
    {
        $response = $this->get(route('dashboard', ['direction' => 'sell']));

        $response->assertOk();
        $response->assertSee('No trades found for the selected filters.');
    }

    public function test_dashboard_rejects_custom_range_that_ends_before_it_starts(): void
    {
        $response = $this->from(route('dashboard'))->get(route('dashboard', [
            'period' => 'custom',
            'date_from' => '2026-08-28',
            'date_to' => '2026-08-01',
        ]));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrors(['date_to']);
    }
}
