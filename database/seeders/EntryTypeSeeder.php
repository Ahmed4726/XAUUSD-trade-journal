<?php

namespace Database\Seeders;

use App\Models\EntryType;
use Illuminate\Database\Seeder;

class EntryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Impulse',
                'description' => 'Entry taken from a strong price impulse.',
            ],
            [
                'name' => 'Dynamic',
                'description' => 'Entry taken dynamically from evolving price action.',
            ],
            [
                'name' => 'Confirmation',
                'description' => 'Entry taken after price confirms the expected direction.',
            ],
            [
                'name' => 'Breakout',
                'description' => 'Entry taken as price breaks a defined level or range.',
            ],
            [
                'name' => 'Retest',
                'description' => 'Entry taken after a breakout and subsequent retest.',
            ],
            [
                'name' => 'Immediate',
                'description' => 'Entry taken immediately at the planned area without additional confirmation.',
            ],
        ];

        foreach ($types as $type) {
            EntryType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'description' => $type['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
