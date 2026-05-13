<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HeroSlide;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        HeroSlide::insert([
            [
                'badge_text' => 'TRENDING NOW',
                'title' => 'SSC CGL 2025',
                'description' => "Target 350+ Score with India's most attempted mock series.",
                'button_text' => 'View Test Series',
                'theme_color' => 'var(--brand-blue)',
                'bg_gradient_start' => 'var(--brand-blue)',
                'bg_gradient_end' => '#60a5fa',
                'icon_top' => '🏛️',
                'icon_bottom' => '🇮🇳',
                'sort_order' => 1
            ],
            [
                'badge_text' => 'NEW BATCH',
                'title' => 'RRB ALP 2025',
                'description' => 'Complete Technical + Non-Tech coverage.',
                'button_text' => 'Enroll Now',
                'theme_color' => 'var(--brand-pink)', // Ensure this CSS var exists or use hex
                'bg_gradient_start' => 'var(--brand-pink)',
                'bg_gradient_end' => '#f472b6',
                'icon_top' => '🚆',
                'icon_bottom' => '🔧',
                'sort_order' => 2
            ],
            [
                'badge_text' => 'ADMISSIONS OPEN',
                'title' => 'Banking Elite',
                'description' => 'One Pass for SBI PO, IBPS & RBI Grade B.',
                'button_text' => 'Get Started',
                'theme_color' => 'var(--brand-green)',
                'bg_gradient_start' => 'var(--brand-green)',
                'bg_gradient_end' => '#a3e635',
                'icon_top' => '🏦',
                'icon_bottom' => '📊',
                'sort_order' => 3
            ],
        ]);
    }
}
