<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feature;
use App\Models\SubCategory;
use App\Models\Plan; // Make sure to import Plan
use App\Settings\HomePageSettings;
use App\Settings\PaymentSettings;
use App\Settings\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    /**
     * Welcome page
     */
    public function index(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {
        try {
            // ---------------------------------------------------------
            // 1. DYNAMIC DATA: Fetch Categories -> SubCategories -> Plans
            // ---------------------------------------------------------
            $categories = Category::where('is_active', true)
                ->with(['subCategories' => function($q) {
                    $q->where('is_active', true)
                      ->with(['plans' => function($pq) {
                          // Fetch Active Plans sorted by their order
                          $pq->where('is_active', true)
                             ->orderBy('sort_order', 'asc')
                             ->orderBy('created_at', 'desc');
                      }]);
                }])
                ->orderBy('name', 'asc') // Sort Categories by Name (since sort_order missing in DB)
                ->get();

            // Default Active Tab (First Category Name)
            $defaultTab = $categories->first()->name ?? '';

            // ---------------------------------------------------------
            // 2. STATIC DATA (For Stats, Footer, etc.)
            // ---------------------------------------------------------
            $stats = [
                ['count' => '53,567', 'label' => 'Total Selections', 'icon' => '🏆', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
                ['count' => '19,054', 'label' => 'Selections in SSC', 'icon' => '🏛️', 'color' => 'text-brand-blue', 'bg' => 'bg-blue-100'],
                ['count' => '18,921', 'label' => 'Selections in Banking', 'icon' => '🏦', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
                ['count' => '7,087', 'label' => 'Selections in Railways', 'icon' => '🚆', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
                ['count' => '8,505', 'label' => 'Other Govt Exams', 'icon' => '🎖️', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
            ];

            // Bottom "Popular Test Series" Section (Static for now)
            $popularTestSeries = [
                [
                    'title' => 'SSC GD Constable 2026 Mock Test Series',
                    'users' => '285.9k',
                    'total_tests' => '779',
                    'free_tests' => '11',
                    'languages' => ['English', 'Hindi'],
                    'features' => ['1 Scholarship Test', '7 Live Test'],
                    'more_count' => '+726 more tests',
                ],
                [
                    'title' => 'RRB Group D Mock Test Series',
                    'users' => '2M+',
                    'total_tests' => '2104',
                    'free_tests' => '48',
                    'languages' => ['English', 'Hindi', 'Marathi'],
                    'features' => ['6 Official Mock', 'Exam Day Special'],
                    'more_count' => '+1916 more tests',
                ],
            ];

            // Footer Links
            $allTestSeries = [
                'Popular' => ['JEE Main 2025', 'CUET 2025', 'NEET 2025', 'SSC GD'],
                'Engineering' => ['GATE 2025', 'SSC JE', 'RRB JE'],
                'Banking' => ['SBI PO', 'IBPS PO', 'RBI Grade B'],
                'SSC & Railways' => ['SSC CGL', 'SSC CHSL', 'RRB NTPC'],
            ];

            // ---------------------------------------------------------
            // 3. RETURN VIEW
            // ---------------------------------------------------------
            return view('store.index', [
                'siteSettings' => $siteSettings,
                'homePageSettings' => $homePageSettings,
                'categories' => $categories,       // Dynamic Tabs Data
                'defaultTab' => $defaultTab,       // Dynamic Default Tab
                'stats' => $stats,
                'popularTestSeries' => $popularTestSeries,
                'allTestSeries' => $allTestSeries
            ]);

        } catch (\Throwable $e) {
            Log::error('Exam Babu - Home Page Error: ' . $e->getMessage());
            abort(500, 'Something went wrong while loading the home page.');
        }
    }

    /**
     * Explore category page
     */
    public function explore(
        string $slug,
        HomePageSettings $homePageSettings,
        SiteSettings $siteSettings,
        PaymentSettings $paymentSettings
    ): View {
        try {
            $category = SubCategory::with(['plans' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with('features');
            }])->where('slug', $slug)->firstOrFail();

            $features = Feature::orderBy('sort_order')->get();

            $leastPrice = 0;
            if ($category->plans->isNotEmpty()) {
                $leastPrice = formatPrice(
                    $category->plans->min('price'),
                    $paymentSettings->currency_symbol,
                    $paymentSettings->currency_symbol_position
                );
            }

            return view('store.explore', [
                'category' => $category,
                'least_price' => $leastPrice,
                'plans' => $category->plans,
                'features' => $features,
                'siteSettings' => $siteSettings,
                'homePageSettings' => $homePageSettings,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning("Exam Babu - Explore Page: Category not found for slug '{$slug}'");
            abort(404);
        } catch (\Throwable $e) {
            Log::error('Exam Babu - Explore Page Error: ' . $e->getMessage());
            abort(500, 'Unable to load exploration plans.');
        }
    }

    /**
     * Pricing Page
     */
    public function pricing(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {
        try {
            $features = Feature::orderBy('sort_order')->get();

            $categories = SubCategory::whereHas('plans')
                ->with(['category', 'plans' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with('features');
                }])
                ->orderBy('name')
                ->get();

            return view('store.pricing', [
                'categories' => $categories,
                'features' => $features,
                'selectedCategory' => $categories->count() > 0 ? $categories->first()->code : '',
                'siteSettings' => $siteSettings,
                'homePageSettings' => $homePageSettings,
            ]);
        } catch (\Throwable $e) {
            Log::error('Exam Babu - Pricing Page Error: ' . $e->getMessage());
            abort(500, 'Unable to load pricing information.');
        }
    }

    /**
     * Parent category page
     */
    public function category(string $slug, HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {
        try {
            $parentCategory = Category::with(['subCategories' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();

            return view('store.category-subcategories', [
                'parentCategory' => $parentCategory,
                'subCategories' => $parentCategory->subCategories,
                'siteSettings' => $siteSettings,
                'homePageSettings' => $homePageSettings,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning("Exam Babu - Parent Category Page: Category not found for slug '{$slug}'");
            abort(404);
        } catch (\Throwable $e) {
            Log::error('Exam Babu - Parent Category Page Error: ' . $e->getMessage());
            abort(500, 'Unable to load category details.');
        }
    }
}
