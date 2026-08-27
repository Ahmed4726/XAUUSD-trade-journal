<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTradeScreenshotRequest;
use App\Models\Trade;
use App\Models\TradeImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class TradeScreenshotController extends Controller
{
    public function store(StoreTradeScreenshotRequest $request, Trade $trade): RedirectResponse
    {
        foreach ($request->file('screenshots') as $screenshot) {
            $path = $screenshot->store("trade-screenshots/{$trade->id}", 'public');

            $trade->images()->create([
                'path' => $path,
                'original_name' => $screenshot->getClientOriginalName(),
                'type' => $request->string('type')->toString(),
            ]);
        }

        return redirect()->route('trades.show', $trade)->with('success', 'Screenshot uploaded successfully.');
    }

    public function destroy(Trade $trade, TradeImage $tradeImage): RedirectResponse
    {
        abort_unless($tradeImage->trade_id === $trade->id, 404);

        Storage::disk('public')->delete($tradeImage->path);
        $tradeImage->delete();

        return redirect()->route('trades.show', $trade)->with('success', 'Screenshot deleted successfully.');
    }
}
