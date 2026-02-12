<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeStat;

class HomeStatSeeder extends Seeder
{
    public function run(): void
    {
        // Pehle purana data clear karein taaki duplicate na ho
        HomeStat::truncate();

        $stats = [
            [
                'count' => '53,567',
                'label' => 'Total Selections',
                'icon' => '🏆',
                'text_class' => 'text-yellow-600',
                'bg_class' => 'bg-yellow-100',
                'sort_order' => 1
            ],
            [
                'count' => '19,054',
                'label' => 'Selections in SSC',
                'icon' => '🏛️',
                'text_class' => 'text-brand-blue', // Ensure CSS variable or class exists
                'bg_class' => 'bg-blue-100',
                'sort_order' => 2
            ],
            [
                'count' => '18,921',
                'label' => 'Selections in Banking',
                'icon' => '🏦',
                'text_class' => 'text-green-600',
                'bg_class' => 'bg-green-100',
                'sort_order' => 3
            ],
            [
                'count' => '7,087',
                'label' => 'Selections in Railways',
                'icon' => '🚆',
                'text_class' => 'text-orange-600',
                'bg_class' => 'bg-orange-100',
                'sort_order' => 4
            ],
            [
                'count' => '8,505',
                'label' => 'Other Govt Exams',
                'icon' => '🎖️',
                'text_class' => 'text-purple-600',
                'bg_class' => 'bg-purple-100',
                'sort_order' => 5
            ],
        ];

        foreach ($stats as $stat) {
            HomeStat::create($stat);
        }
    }
}
