<?php

namespace Database\Seeders;

use App\Models\TradeMistake;
use Illuminate\Database\Seeder;

class TradeMistakeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Early Entry',
            'Late Entry',
            'FOMO',
            'Entered Without Confirmation',
            'Ignored S/R',
            'Wrong Direction',
            'Moved Stop Loss',
            'Moved Take Profit',
            'Exited Too Early',
            'Exited Too Late',
            'Overtrading',
            'Revenge Trade',
            'Oversized Lot',
            'Chased Price',
            'No Clear Setup',
            'Emotional Entry',
        ] as $name) {
            TradeMistake::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
