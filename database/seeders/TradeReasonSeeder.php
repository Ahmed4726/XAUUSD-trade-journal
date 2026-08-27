<?php

namespace Database\Seeders;

use App\Models\TradeReason;
use Illuminate\Database\Seeder;

class TradeReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            [
                'name' => 'Impulse Entry',
                'description' => 'Entry based on strong price action impulse and momentum.',
            ],
            [
                'name' => 'Potential Rejection',
                'description' => 'Entry based on price showing potential rejection from an important area.',
            ],
            [
                'name' => 'Dynamic Entry',
                'description' => 'Entry taken dynamically based on current price action rather than a fixed level.',
            ],
            [
                'name' => 'Breakout',
                'description' => 'Entry following a clean breakout of an established range or level.',
            ],
            [
                'name' => 'Clean S/R',
                'description' => 'Entry based on clean and clearly respected support or resistance.',
            ],
            [
                'name' => 'Range Breakout',
                'description' => 'Breakout from a clearly defined consolidation or trading range.',
            ],
            [
                'name' => 'Range Rejection',
                'description' => 'Rejection from the upper or lower boundary of a defined range.',
            ],
            [
                'name' => 'Trend Continuation',
                'description' => 'Price action entry aligned with an established trend.',
            ],
            [
                'name' => 'Trend Reversal',
                'description' => 'Price action entry anticipating a reversal from an established move.',
            ],
            [
                'name' => 'Pullback Entry',
                'description' => 'Entry after price pulls back within an established directional move.',
            ],
            [
                'name' => 'Retest Entry',
                'description' => 'Entry after price breaks a level and returns to test it.',
            ],
            [
                'name' => 'Support Rejection',
                'description' => 'Bullish entry following rejection from support.',
            ],
            [
                'name' => 'Resistance Rejection',
                'description' => 'Bearish entry following rejection from resistance.',
            ],
            [
                'name' => 'False Breakout',
                'description' => 'Entry following a failed breakout and return back inside the prior range.',
            ],
            [
                'name' => 'Trendline Break',
                'description' => 'Entry following a clean break of a respected trendline.',
            ],
            [
                'name' => 'Consolidation Break',
                'description' => 'Entry following a break from a period of consolidation.',
            ],
        ];

        foreach ($reasons as $reason) {
            TradeReason::updateOrCreate(
                ['name' => $reason['name']],
                [
                    'description' => $reason['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
