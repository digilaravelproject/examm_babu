<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feature;
use App\Models\SubCategory;
use App\Models\MicroCategory;
// use App\Models\Plan; // Make sure to import Plan
use App\Settings\HomePageSettings;
use App\Settings\PaymentSettings;
use App\Settings\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
// use Illuminate\Support\FacadesLog;
use Illuminate\Http\Request;
use App\Models\Exam;

use App\Models\HeroSlide;
use App\Models\HomeStat;
use App\Models\HomeFeature;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    /**
     * Welcome page
     */
    public function index_old(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {

        try {
            // ---------------------------------------------------------
            // 1. DYNAMIC DATA: Fetch Categories -> SubCategories -> Plans
            // ---------------------------------------------------------
            $categories = Category::where('is_active', true)
                ->with(['subCategories' => function ($q) {
                    $q->where('is_active', true)
                        ->with(['plans' => function ($pq) {
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

    // public function index(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    // {

    //     try {
    //         // ---------------------------------------------------------
    //         // 1. FETCH CATEGORY → SUB → MICRO CATEGORY (UNCHANGED LOGIC)
    //         // ---------------------------------------------------------
    //         // $categories = Category::where('is_active', true)
    //         //     ->with([
    //         //         'subCategories' => function ($q) {
    //         //             $q->where('is_active', true)
    //         //             ->with([
    //         //                 'microCategories' => function ($mq) {
    //         //                     $mq->where('is_active', true)
    //         //                         ->orderBy('name', 'asc');
    //         //                 }
    //         //             ])
    //         //             ->orderBy('name', 'asc');
    //         //         }
    //         //     ])
    //         //     ->orderBy('name', 'asc')
    //         //     ->get();
    //         $categories = Category::where('is_active', true)
    //             ->with([
    //                 'subCategories' => function ($q) {
    //                     $q->where('is_active', true)
    //                         ->with([
    //                             'plans' => function ($query) {
    //                                 $query->where('is_active', true)
    //                                     ->orderBy('sort_order')
    //                                     ->with('features');
    //                             },
    //                             'microCategories' => function ($mq) {
    //                                 $mq->where('is_active', true)
    //                                     ->orderBy('name', 'asc');
    //                             }
    //                         ])
    //                         ->orderBy('name', 'asc');
    //                 }
    //             ])
    //             ->orderBy('name', 'asc')
    //             ->get();

    //         $defaultTab = $categories->first()->name ?? '';

    //         // ---------------------------------------------------------
    //         // 3. STATIC DATA (For Stats, Footer, etc.)
    //         // ---------------------------------------------------------
    //         $stats = [
    //             ['count' => '53,567', 'label' => 'Total Selections', 'icon' => '🏆', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
    //             ['count' => '19,054', 'label' => 'Selections in SSC', 'icon' => '🏛️', 'color' => 'text-brand-blue', 'bg' => 'bg-blue-100'],
    //             ['count' => '18,921', 'label' => 'Selections in Banking', 'icon' => '🏦', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
    //             ['count' => '7,087', 'label' => 'Selections in Railways', 'icon' => '🚆', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
    //             ['count' => '8,505', 'label' => 'Other Govt Exams', 'icon' => '🎖️', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
    //         ];

    //         // ---------------------------------------------------------
    //         // 4. RETURN VIEW
    //         // ---------------------------------------------------------
    //         return view('store.index', [
    //             'siteSettings'      => $siteSettings,
    //             'homePageSettings'  => $homePageSettings,
    //             'categories'        => $categories,
    //             'defaultTab'        => $defaultTab,
    //             'stats'             => $stats,
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error('Exam Babu - Home Page Error: ' . $e->getMessage());
    //         abort(500, 'Something went wrong while loading the home page.');
    //     }
    // }


    public function index(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {
        try {
            $heroSlides = HeroSlide::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $categories = Category::where('is_active', true)
                ->with([
                    'subCategories' => function ($q) {
                        $q->where('is_active', true)
                            ->with([
                                'plans' => function ($query) {
                                    $query->where('is_active', true)
                                        ->orderBy('sort_order')
                                        ->with('features');
                                },
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

            $defaultTab = $categories->first()->name ?? '';

            $stats = HomeStat::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            $features = HomeFeature::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            return view('store.index', [
                'siteSettings'      => $siteSettings,
                'homePageSettings'  => $homePageSettings,
                'categories'        => $categories,
                'defaultTab'        => $defaultTab,
                'stats'             => $stats,
                'heroSlides'        => $heroSlides,
                'features'          => $features, // <-- Added here
            ]);
        } catch (\Throwable $e) {
            Log::error('Exam Babu - Home Page Error: ' . $e->getMessage());
            abort(500, 'Something went wrong while loading the home page.');
        }
    }





    /**
     * Explore category page
     */ public function explore(
        string $slug,
        HomePageSettings $homePageSettings,
        SiteSettings $siteSettings,
        PaymentSettings $paymentSettings
    ): View {
        try {
            // 1. Fetch SubCategory by slug
            $subCategory = SubCategory::where('slug', $slug)->firstOrFail();

            // 2. Fetch all MicroCategories under this SubCategory with their Plans
            $microCategories = MicroCategory::where('sub_category_id', $subCategory->id)
                ->with(['plans' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with('features');
                }])
                ->whereHas('plans', function ($q) {
                    $q->where('is_active', true);
                })
                ->get();

            // 3. Fetch Features (for benefits section if needed)
            $features = Feature::orderBy('sort_order')->get();

            // 4. Calculate Least Price from all plans
            $allPlans = $microCategories->flatMap->plans;
            $leastPrice = 0;
            if ($allPlans->isNotEmpty()) {
                $leastPrice = formatPrice(
                    $allPlans->min('price'),
                    $paymentSettings->currency_symbol,
                    $paymentSettings->currency_symbol_position
                );
            }

            // 5. Return View with ALL required variables
            return view('store.explore', [
                'category'         => $subCategory,
                'microCategories'  => $microCategories,
                'selectedCategory' => $subCategory->code ?? $subCategory->slug,
                'least_price'      => $leastPrice,
                'plans'            => $allPlans,
                'features'         => $features,
                'siteSettings'     => $siteSettings,
                'homePageSettings' => $homePageSettings,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning("Exam Babu - Explore Page: SubCategory not found for slug '{$slug}'");
            abort(404);
        } catch (\Throwable $e) {
            Log::error('Exam Babu - Explore Page Error: ' . $e->getMessage());
            abort(500, 'Unable to load exploration plans.');
        }
    }

    /**
     * Pricing Page
     */
    public function pricing_old(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
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

    public function pricing(Request $request, HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {
        $microCategory = array_key_first($request->all());
        try {
            $features = Feature::orderBy('sort_order')->get();

            $categoriesQuery = MicroCategory::whereHas('plans', function ($q) {
                $q->where('is_active', true);
            })
                ->with(['subCategory', 'plans' => function ($query) use ($microCategory) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with('features');

                    // Filter plans by microcategory
                    if ($microCategory) {
                        $query->where('category_id', $microCategory);
                    }
                }])
                ->orderBy('name');

            // Filter category itself
            if ($microCategory) {
                $categoriesQuery->where('id', $microCategory);
            }

            $categories = $categoriesQuery->get();

            return view('store.pricing', [
                'categories'       => $categories,
                'features'         => $features,
                'selectedCategory' => $categories->first()->code ?? '',
                'siteSettings'     => $siteSettings,
                'homePageSettings' => $homePageSettings,
            ]);
        } catch (\Throwable $e) {
            Log::error('Pricing Page Error: ' . $e->getMessage());
            abort(500, 'Unable to load pricing information.');
        }
    }

    // public function exam_details(
    //     Request $request,
    //     HomePageSettings $homePageSettings,
    //     SiteSettings $siteSettings
    // ) {
    //     try {

    //         $subCategory   = (int) $request->route('subCategory');
    //         $microCategory = (int) $request->route('microCategory');

    //         // Base exam query
    //         $examsQuery = Exam::where('sub_category_id', $subCategory)
    //             ->with(['subCategory', 'microCategory'])
    //             ->orderBy('title');

    //         // Filter by micro category if provided
    //         if ($microCategory) {
    //             $examsQuery->where('micro_category_id', $microCategory);
    //         }

    //         $exams = $examsQuery->get();

    //         // echo'<pre>';print_r($exams->count());echo'<br>';exit;

    //         return view('store.exam_details', [
    //             'exams'            => $exams,
    //             'subCategory'      => SubCategory::findOrFail($subCategory),
    //             'microCategory'    => $microCategory
    //                 ? MicroCategory::find($microCategory)
    //                 : null,
    //             'siteSettings'     => $siteSettings,
    //             'homePageSettings' => $homePageSettings,
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error('Exam Details Error: ' . $e->getMessage());
    //         abort(500, 'Unable to load exams.');
    //     }
    // }


    public function exam_details(
        Request $request,
        HomePageSettings $homePageSettings,
        SiteSettings $siteSettings
    ) {
        try {
            $subCategoryId   = (int) $request->route('subCategory');
            $microCategoryId = (int) $request->route('microCategory');

            // 1. Fetch Exams
            $examsQuery = Exam::where('sub_category_id', $subCategoryId)
                ->with(['subCategory', 'microCategory'])
                ->orderBy('title');

            // Filter by micro category if provided
            if ($microCategoryId) {
                $examsQuery->where('micro_category_id', $microCategoryId);
            }

            $exams = $examsQuery->get();

            // 2. Fetch Models for view
            $subCategoryModel = SubCategory::findOrFail($subCategoryId);
            $microCategoryModel = $microCategoryId ? MicroCategory::with(['plans' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])->find($microCategoryId) : null;

            return view('store.exam_details', [
                'exams'            => $exams,
                'subCategory'      => $subCategoryModel,
                'microCategory'    => $microCategoryModel,
                'siteSettings'     => $siteSettings,
                'homePageSettings' => $homePageSettings,
            ]);
        } catch (\Throwable $e) {
            Log::error('Exam Details Error: ' . $e->getMessage());
            abort(500, 'Unable to load exams.');
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
