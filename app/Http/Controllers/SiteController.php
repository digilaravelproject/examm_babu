<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Category;
use App\Models\SubCategory;
use App\Settings\HomePageSettings;
use App\Settings\PaymentSettings;
use App\Settings\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    /**
     * Welcome page
     */
    public function index(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {
        try {
            return view('store.index', [
                'siteSettings' => $siteSettings,
                'homePageSettings' => $homePageSettings
            ]);
            // return view('welcome');
        } catch (\Throwable $e) {
            Log::error("Exam Babu - Home Page Error: " . $e->getMessage());
            abort(500, 'Something went wrong while loading the home page.');
        }
    }

    /**
     * Explore category page (Shows plans for a sub-category)
     */
    public function explore(
        string $slug,
        HomePageSettings $homePageSettings,
        SiteSettings $siteSettings,
        PaymentSettings $paymentSettings
    ): View {
        try {
            // Data fetch with Eager Loading (Plans aur unke Features sath me ayenge)
            $category = SubCategory::with(['plans' => function ($query) {
                $query->where('is_active', true)
                      ->orderBy('sort_order')
                      ->with('features'); // Plan ke features bhi load kar liye
            }])->where('slug', $slug)->firstOrFail();

            // Saare available features list (Display ke liye agar chahiye ho)
            $features = Feature::orderBy('sort_order')->get();

            // Minimum price calculate karna safe tarike se
            $leastPrice = 0;
            if ($category->plans->isNotEmpty()) {
                $leastPrice = formatPrice(
                    $category->plans->min('price'),
                    $paymentSettings->currency_symbol,
                    $paymentSettings->currency_symbol_position
                );
            }

            return view('store.explore', [
                'category' => $category, // Pura model bhej diya, Blade me jo chahiye use kar lo
                'least_price' => $leastPrice,
                'plans' => $category->plans, // Direct Collection pass kiya (No Transformer)
                'features' => $features, // Features bhi pass kar diye agar compare karna ho
                'siteSettings' => $siteSettings,
                'homePageSettings' => $homePageSettings
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning("Exam Babu - Explore Page: Category not found for slug '{$slug}'");
            abort(404);
        } catch (\Throwable $e) {
            Log::error("Exam Babu - Explore Page Error: " . $e->getMessage());
            abort(500, 'Unable to load exploration plans.');
        }
    }

    /**
     * Pricing Page (Shows all categories and plans)
     */
    public function pricing(HomePageSettings $homePageSettings, SiteSettings $siteSettings): View
    {
        try {
            // Saare features load kiye
            $features = Feature::orderBy('sort_order')->get();

            // Sirf wahi categories layenge jinke paas active plans hain
            $categories = SubCategory::whereHas('plans')
                ->with(['category', 'plans' => function ($query) {
                    $query->where('is_active', true)
                          ->orderBy('sort_order')
                          ->with('features');
                }])
                ->orderBy('name')
                ->get();

            return view('store.pricing', [
                'categories' => $categories, // Direct Data (No Transformer)
                'features' => $features,     // Comparison table ke liye
                'selectedCategory' => $categories->count() > 0 ? $categories->first()->code : '',
                'siteSettings' => $siteSettings,
                'homePageSettings' => $homePageSettings
            ]);

        } catch (\Throwable $e) {
            Log::error("Exam Babu - Pricing Page Error: " . $e->getMessage());
            abort(500, 'Unable to load pricing information.');
        }
    }

    /**
     * Parent category page – shows all subcategories of a parent category
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
                'parentCategory'   => $parentCategory,
                'subCategories'    => $parentCategory->subCategories,
                'siteSettings'     => $siteSettings,
                'homePageSettings' => $homePageSettings,
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning("Exam Babu - Parent Category Page: Category not found for slug '{$slug}'");
            abort(404);
        } catch (\Throwable $e) {
            Log::error("Exam Babu - Parent Category Page Error: " . $e->getMessage());
            abort(500, 'Unable to load category details.');
        }
    }
}
