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
            $examCategories[$category->name] = [
                'id'    => $category->id,
                'slug'  => $category->slug,
                'icon'  => $category->image_path ? asset($category->image_path) : null,
                'first_letter' => substr($category->name, 0, 1),

                'subcategories' => $category->subCategories->map(function ($sub) {
                    return [
                        'id'   => $sub->id,
                        'name' => $sub->name,
                        'slug' => $sub->slug ?? '#',

                        'micro_categories' => $sub->microCategories->map(function ($micro) {
                            return [
                                'id'   => $micro->id,
                                'name' => $micro->name,
                                'slug' => $micro->slug ?? '#',
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
