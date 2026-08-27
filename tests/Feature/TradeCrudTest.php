<?php

namespace Tests\Feature;

use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeReason;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TradeCrudTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_trade_is_created_with_server_side_calculations(): void
    {
        [$reason, $entryType] = $this->formOptions();

        $response = $this->post(route('trades.store'), $this->payload($reason, $entryType));

        $response->assertRedirectToRoute('trades.index');
        $this->assertDatabaseHas('trades', [
            'symbol' => 'XAUUSD',
            'weekday' => 'Wednesday',
            'market_condition' => 'trending',
            'risk_amount' => 50,
            'potential_profit' => 100,
            'risk_reward' => 2,
            'status' => 'open',
        ]);
    }

    public function test_invalid_buy_levels_are_rejected(): void
    {
        [$reason, $entryType] = $this->formOptions();
        $payload = $this->payload($reason, $entryType, ['stop_loss' => 3351.00]);

        $response = $this->from(route('trades.create'))->post(route('trades.store'), $payload);

        $response->assertRedirect(route('trades.create'));
        $response->assertSessionHasErrors(['stop_loss']);
        $this->assertDatabaseCount('trades', 0);
    }

    public function test_trades_page_renders_and_keeps_filter_values(): void
    {
        $response = $this->get(route('trades.index', [
            'direction' => 'buy',
            'timeframe' => '1h',
            'status' => 'open',
        ]));

        $response->assertOk();
        $response->assertSee('Trade journal');
        $response->assertSee('value="buy" selected', false);
    }

    public function test_entering_exit_price_derives_a_winning_result(): void
    {
        [$reason, $entryType] = $this->formOptions();
        $trade = Trade::create([
            'trade_reason_id' => $reason->id,
            'entry_type_id' => $entryType->id,
            'symbol' => 'XAUUSD',
            'trade_date' => '2026-08-26',
            'weekday' => 'Wednesday',
            'direction' => 'buy',
            'timeframe' => '1h',
            'market_condition' => 'trending',
            'entry_price' => 3350.00,
            'stop_loss' => 3345.00,
            'take_profit' => 3360.00,
            'lot_size' => 0.10,
            'contract_size' => 100,
            'risk_amount' => 50,
            'potential_profit' => 100,
            'risk_reward' => 2,
        ]);

        $response = $this->put(route('trades.update', $trade), $this->payload($reason, $entryType, ['exit_price' => 3353.00]));

        $response->assertRedirectToRoute('trades.show', $trade);
        $this->assertDatabaseHas('trades', ['id' => $trade->id, 'actual_profit_loss' => 30, 'status' => 'win']);
    }

    private function formOptions(): array
    {
        return [
            TradeReason::create(['name' => 'Breakout', 'is_active' => true]),
            EntryType::create(['name' => 'Confirmation', 'is_active' => true]),
        ];
    }

    private function payload(TradeReason $reason, EntryType $entryType, array $overrides = []): array
    {
        return array_merge([
            'trade_date' => '2026-08-26',
            'trade_time' => '09:30',
            'direction' => 'buy',
            'timeframe' => '1h',
            'trade_reason_id' => $reason->id,
            'entry_type_id' => $entryType->id,
            'market_condition' => 'trending',
            'entry_price' => 3350.00,
            'stop_loss' => 3345.00,
            'take_profit' => 3360.00,
            'lot_size' => 0.10,
        ], $overrides);
    }
}
