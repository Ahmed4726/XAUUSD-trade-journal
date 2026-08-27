<?php

namespace Tests\Feature;

use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeMistake;
use App\Models\TradeReason;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TradeMistakeManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_trade_mistake_can_be_created_updated_and_deactivated(): void
    {
        $response = $this->post(route('trade-mistakes.store'), [
            'name' => 'Early Entry',
            'description' => 'Entered before the setup confirmed.',
            'is_active' => true,
        ]);

        $response->assertRedirectToRoute('trade-mistakes.index');
        $mistake = TradeMistake::firstOrFail();
        $this->assertDatabaseHas('trade_mistakes', ['name' => 'Early Entry', 'is_active' => 1]);

        $response = $this->put(route('trade-mistakes.update', $mistake), [
            'name' => 'Early Entry',
            'description' => 'Entered before a confirmation candle.',
            'is_active' => false,
        ]);

        $response->assertRedirectToRoute('trade-mistakes.show', $mistake);
        $this->assertDatabaseHas('trade_mistakes', ['id' => $mistake->id, 'is_active' => 0]);
    }

    public function test_referenced_mistake_is_deactivated_instead_of_deleted(): void
    {
        $mistake = TradeMistake::create(['name' => 'FOMO', 'is_active' => true]);
        $this->createTrade($mistake);

        $response = $this->delete(route('trade-mistakes.destroy', $mistake));

        $response->assertRedirectToRoute('trade-mistakes.index');
        $this->assertDatabaseHas('trade_mistakes', ['id' => $mistake->id, 'is_active' => 0]);
    }

    public function test_trade_can_store_an_optional_mistake(): void
    {
        $mistake = TradeMistake::create(['name' => 'Late Entry', 'is_active' => true]);
        $trade = $this->createTrade($mistake);

        $trade->load('mistake');

        $this->assertSame('Late Entry', $trade->mistake?->name);
    }

    private function createTrade(?TradeMistake $mistake = null): Trade
    {
        $reason = TradeReason::create(['name' => 'Breakout', 'is_active' => true]);
        $entryType = EntryType::create(['name' => 'Confirmation', 'is_active' => true]);

        return Trade::create([
            'trade_reason_id' => $reason->id,
            'trade_mistake_id' => $mistake?->id,
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
        ]);
    }
}
