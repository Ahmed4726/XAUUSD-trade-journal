<?php

namespace Tests\Feature;

use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeImage;
use App\Models\TradeReason;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TradeScreenshotTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_screenshots_are_uploaded_and_displayed_on_the_trade(): void
    {
        Storage::fake('public');
        $trade = $this->createTrade();

        $response = $this->post(route('trades.screenshots.store', $trade), [
            'screenshots' => [UploadedFile::fake()->image('entry-chart.png', 1200, 800)],
            'type' => 'entry',
        ]);

        $response->assertRedirectToRoute('trades.show', $trade);
        $image = TradeImage::firstOrFail();
        $this->assertSame('entry', $image->type);
        Storage::disk('public')->assertExists($image->path);

        $this->get(route('trades.show', $trade))->assertSee('entry-chart.png');
    }

    public function test_invalid_screenshot_is_rejected(): void
    {
        $trade = $this->createTrade();

        $response = $this->from(route('trades.show', $trade))->post(route('trades.screenshots.store', $trade), [
            'screenshots' => [UploadedFile::fake()->create('notes.txt', 10, 'text/plain')],
            'type' => 'before',
        ]);

        $response->assertRedirect(route('trades.show', $trade));
        $response->assertSessionHasErrors(['screenshots.0']);
        $this->assertDatabaseCount('trade_images', 0);
    }

    public function test_screenshot_and_file_are_deleted_together(): void
    {
        Storage::fake('public');
        $trade = $this->createTrade();
        $image = $trade->images()->create(['path' => 'trade-screenshots/'.$trade->id.'/chart.png', 'original_name' => 'chart.png', 'type' => 'after']);
        Storage::disk('public')->put($image->path, 'image-content');

        $response = $this->delete(route('trades.screenshots.destroy', [$trade, $image]));

        $response->assertRedirectToRoute('trades.show', $trade);
        $this->assertModelMissing($image);
        Storage::disk('public')->assertMissing('trade-screenshots/'.$trade->id.'/chart.png');
    }

    public function test_screenshot_from_another_trade_cannot_be_deleted(): void
    {
        Storage::fake('public');
        $trade = $this->createTrade();
        $otherTrade = $this->createTrade('Retest', 'Immediate');
        $image = $otherTrade->images()->create(['path' => 'trade-screenshots/'.$otherTrade->id.'/chart.png', 'type' => 'before']);
        Storage::disk('public')->put($image->path, 'image-content');

        $response = $this->delete(route('trades.screenshots.destroy', [$trade, $image]));

        $response->assertNotFound();
        $this->assertModelExists($image);
        Storage::disk('public')->assertExists($image->path);
    }

    private function createTrade(string $reasonName = 'Breakout', string $entryTypeName = 'Confirmation'): Trade
    {
        $reason = TradeReason::create(['name' => $reasonName, 'is_active' => true]);
        $entryType = EntryType::create(['name' => $entryTypeName, 'is_active' => true]);

        return Trade::create([
            'trade_reason_id' => $reason->id,
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
