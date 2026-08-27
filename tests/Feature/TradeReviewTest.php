<?php

namespace Tests\Feature;

use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeReason;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TradeReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_review_fields_and_execution_rating_are_saved_and_displayed(): void
    {
        [$reason, $entryType] = $this->formOptions();
        $payload = $this->payload($reason, $entryType, [
            'why_entered' => 'Price rejected clean support.',
            'confirmation_seen' => 'Strong bullish rejection.',
            'expected_scenario' => 'Continuation toward resistance.',
            'plan_vs_reality' => 'Price reached target as expected.',
            'what_went_well' => 'Risk was respected.',
            'what_went_wrong' => 'Entered slightly early.',
            'lesson_learned' => 'Wait for stronger confirmation.',
            'execution_rating' => 4,
        ]);

        $this->post(route('trades.store'), $payload)->assertRedirectToRoute('trades.index');
        $trade = Trade::firstOrFail();

        $this->assertDatabaseHas('trades', ['id' => $trade->id, 'why_entered' => 'Price rejected clean support.', 'execution_rating' => 4]);
        $this->get(route('trades.show', $trade))->assertSee('Price rejected clean support.')->assertSee('4 / 5 — Good');
    }

    public function test_review_fields_are_nullable(): void
    {
        [$reason, $entryType] = $this->formOptions();

        $this->post(route('trades.store'), $this->payload($reason, $entryType))->assertRedirectToRoute('trades.index');
        $trade = Trade::firstOrFail();

        $this->assertNull($trade->why_entered);
        $this->assertNull($trade->execution_rating);
        $this->get(route('trades.show', $trade))->assertSee('No execution rating recorded.')->assertSee('Not recorded yet.');
    }

    public function test_execution_ratings_one_and_five_are_accepted(): void
    {
        [$reason, $entryType] = $this->formOptions();
        $this->post(route('trades.store'), $this->payload($reason, $entryType, ['execution_rating' => 1]))->assertRedirect();
        $this->post(route('trades.store'), $this->payload($reason, $entryType, ['execution_rating' => 5, 'trade_date' => '2026-08-27']))->assertRedirect();

        $this->assertDatabaseCount('trades', 2);
    }

    public function test_invalid_execution_ratings_are_rejected(): void
    {
        [$reason, $entryType] = $this->formOptions();

        foreach ([0, 6, 'good'] as $rating) {
            $response = $this->from(route('trades.create'))->post(route('trades.store'), $this->payload($reason, $entryType, ['execution_rating' => $rating]));
            $response->assertRedirect(route('trades.create'));
            $response->assertSessionHasErrors(['execution_rating']);
        }

        $this->assertDatabaseCount('trades', 0);
    }

    public function test_review_fields_can_be_updated_without_changing_derived_trade_result(): void
    {
        [$reason, $entryType] = $this->formOptions();
        $trade = Trade::create(array_merge($this->payload($reason, $entryType), ['weekday' => 'Wednesday', 'symbol' => 'XAUUSD', 'contract_size' => 100, 'risk_amount' => 50, 'potential_profit' => 100, 'risk_reward' => 2, 'exit_price' => 3353, 'actual_profit_loss' => 30, 'status' => 'win']));

        $this->put(route('trades.update', $trade), $this->payload($reason, $entryType, ['exit_price' => 3353, 'what_went_well' => 'Risk respected.', 'execution_rating' => 5]))->assertRedirectToRoute('trades.show', $trade);

        $this->assertDatabaseHas('trades', ['id' => $trade->id, 'actual_profit_loss' => 30, 'status' => 'win', 'what_went_well' => 'Risk respected.', 'execution_rating' => 5]);
    }

    private function formOptions(): array
    {
        return [TradeReason::create(['name' => 'Breakout', 'is_active' => true]), EntryType::create(['name' => 'Confirmation', 'is_active' => true])];
    }

    private function payload(TradeReason $reason, EntryType $entryType, array $overrides = []): array
    {
        return array_merge(['trade_date' => '2026-08-26', 'trade_time' => '09:30', 'direction' => 'buy', 'timeframe' => '1h', 'trade_reason_id' => $reason->id, 'entry_type_id' => $entryType->id, 'market_condition' => 'trending', 'entry_price' => 3350, 'stop_loss' => 3345, 'take_profit' => 3360, 'lot_size' => 0.10], $overrides);
    }
}
