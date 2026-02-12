<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HomeFeature;

class HomeFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HomeFeature::truncate();
        $features = [
            ['title' => 'Exam Oriented', 'description' => 'Content designed purely based on latest exam patterns and syllabus.', 'icon' => '🎯', 'bg_class' => 'bg-blue-100', 'sort_order' => 1],
            ['title' => 'Smart Analytics', 'description' => 'Get detailed report cards, strong/weak area analysis after every test.', 'icon' => '📊', 'bg_class' => 'bg-green-100', 'sort_order' => 2],
            ['title' => 'Bilingual', 'description' => 'Switch between English and Hindi (or Marathi) anytime during the test.', 'icon' => '🗣️', 'bg_class' => 'bg-purple-100', 'sort_order' => 3],
            ['title' => 'Affordable', 'description' => 'Premium quality education at the most affordable prices in India.', 'icon' => '💸', 'bg_class' => 'bg-orange-100', 'sort_order' => 4],
        ];
        foreach ($features as $f) {
            HomeFeature::create($f);
        }
    }
}
