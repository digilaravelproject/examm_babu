<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class NavbarComposer
{
    public function compose(View $view)
    {
        $categories = Category::where('is_active', true)
            ->with([
                'subCategories' => function ($q) {
                    $q->where('is_active', true)
                        ->with([
                            'microCategories' => function ($mq) {
                                $mq->where('is_active', true)
                                    ->orderBy('name', 'asc');
                            }
                        ])
                        ->orderBy('name', 'asc');
                }
            ])
            ->orderBy('name', 'asc')
            ->get();

        $iconMap = [
            'Police Exams'   => '👮',
            'SSC Exams'      => '🏛️',
            'Banking Exams'  => '🏦',
            'Teaching Exams' => '👨‍🏫',
            'Civil Services' => '🇮🇳',
            'Railways Exams' => '🚆',
            'Engineering'    => '🏗️',
            'Defence Exams'  => '🎖️',
        ];

        $examCategories = [];

        foreach ($categories as $category) {

            $icon = $iconMap[$category->name] ?? null;

            $examCategories[$category->name] = [
                'icon' => $icon ?: asset('storage/site_images/def_cat_logo.jpg'),

                'subcategories' => $category->subCategories->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'slug' => $sub->slug ?? '#',

                        'micro_categories' => $sub->microCategories->map(function ($micro) {
                            return [
                                'id'   => $micro->id,
                                'name' => $micro->name,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        }

        // Inject into view
        $view->with('examCategories', $examCategories);
    }
}
