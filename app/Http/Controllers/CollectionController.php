<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use App\Services\ProductGalleryService;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
use Illuminate\View\View;
/**
 * CollectionController
 * 
 * HTTP controller handling CollectionController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class CollectionController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Collection::withTranslations()
            ->visible()
            ->ordered()
            ->with([
                'products' => function ($productQuery) {
                    $productQuery
                        ->published()
                        ->with(['media', 'brand', 'categories'])
                        ->orderByDesc('published_at')
                        ->limit(4);
                },
            ])
            ->withCount([
                'products as published_products_count' => function ($countQuery) {
                    $countQuery->published();
                },
            ]);
        // Apply filters
        if ($request->filled('type')) {
            $query->where('is_automatic', $request->get('type') === 'automatic');
        }
        if ($request->filled('display_type')) {
            $query->where('display_type', $request->get('display_type'));
        }
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            });
        }
        $collections = PaginationService::paginateWithContext($query, 'collections');
        return view('collections.index', compact('collections'));
    }
    /**
     * Display the specified resource with related data.
     * @param Collection $collection
     * @return View
     */
    public function show(Collection $collection): View
    {
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$collection->relationLoaded('products.images') || !$collection->relationLoaded('products.translations')) {
            $collection->load(['products' => function ($query) {
                $query->published()->with(['images', 'translations']);
            }]);
        }
        $relatedCollections = Collection::withTranslations()->visible()->where('id', '!=', $collection->id)->where('display_type', $collection->display_type)->limit(4)->get()->skipWhile(function ($relatedCollection) {
            // Skip related collections that are not properly configured
            return empty($relatedCollection->name) || !$relatedCollection->is_visible || empty($relatedCollection->slug) || $relatedCollection->products()->count() <= 0;
        });
        // Use splitIn method for better product organization
        $galleryService = new ProductGalleryService();
        $organizedProducts = $galleryService->arrangeForCollection($collection->products, (int) ($collection->display_type ?? 1));
        $products = $collection->products;
        // For backward compatibility with tests
        return view('collections.show', compact('collection', 'relatedCollections', 'organizedProducts', 'products'));
    }
    // API Endpoints
    /**
     * Handle api functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function api(Request $request): JsonResponse
    {
        $query = Collection::withTranslations()->visible();
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            });
        }
        $collections = $query->limit(20)->get();
        return response()->json(['collections' => $collections->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || !$collection->is_visible || empty($collection->slug);
        })->map(function ($collection) {
            return ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'description' => $collection->getTranslatedDescription(), 'image_url' => $collection->getImageUrl(), 'products_count' => $collection->getProductsCountAttribute(), 'display_type' => $collection->display_type, 'is_automatic' => $collection->is_automatic];
        })]);
    }
    /**
     * Handle byType functionality with proper error handling.
     * @param string $type
     * @return JsonResponse
     */
    public function byType(string $type): JsonResponse
    {
        $isAutomatic = $type === 'automatic';
        $collections = Collection::withTranslations()->visible()->where('is_automatic', $isAutomatic)->orderBy('sort_order')->get();
        return response()->json(['collections' => $collections->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || !$collection->is_visible || empty($collection->slug);
        })->map(function ($collection) {
            return ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'description' => $collection->getTranslatedDescription(), 'image_url' => $collection->getImageUrl(), 'products_count' => $collection->getProductsCountAttribute(), 'display_type' => $collection->display_type];
        })]);
    }
    /**
     * Handle withProducts functionality with proper error handling.
     * @return JsonResponse
     */
    public function withProducts(): JsonResponse
    {
        $collections = Collection::withTranslations()->visible()->has('products')->withCount('products')->orderBy('products_count', 'desc')->limit(10)->get();
        return response()->json(['collections' => $collections->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || !$collection->is_visible || empty($collection->slug) || $collection->products_count <= 0;
        })->map(function ($collection) {
            return ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'description' => $collection->getTranslatedDescription(), 'image_url' => $collection->getImageUrl(), 'products_count' => $collection->products_count, 'display_type' => $collection->display_type, 'is_automatic' => $collection->is_automatic];
        })]);
    }
    /**
     * Handle popular functionality with proper error handling.
     * @return JsonResponse
     */
    public function popular(): JsonResponse
    {
        $collections = Collection::withTranslations()->visible()->withCount('products')->orderBy('products_count', 'desc')->limit(8)->get();
        return response()->json(['collections' => $collections->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || !$collection->is_visible || empty($collection->slug) || $collection->products_count <= 0;
        })->map(function ($collection) {
            return ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'description' => $collection->getTranslatedDescription(), 'image_url' => $collection->getImageUrl('sm'), 'products_count' => $collection->products_count, 'display_type' => $collection->display_type];
        })]);
    }
    /**
     * Handle statistics functionality with proper error handling.
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $totalCollections = Collection::count();
        $visibleCollections = Collection::visible()->count();
        $automaticCollections = Collection::where('is_automatic', true)->count();
        $manualCollections = Collection::where('is_automatic', false)->count();
        $collectionsWithProducts = Collection::has('products')->count();
        return response()->json(['total_collections' => $totalCollections, 'visible_collections' => $visibleCollections, 'automatic_collections' => $automaticCollections, 'manual_collections' => $manualCollections, 'collections_with_products' => $collectionsWithProducts, 'collections_without_products' => $totalCollections - $collectionsWithProducts]);
    }
    /**
     * Handle products functionality with proper error handling.
     * @param Collection $collection
     * @return JsonResponse
     */
    public function products(Collection $collection): JsonResponse
    {
        $products = PaginationService::paginateQueryWithSkipWhile($collection->products()->published()->with(['images', 'translations'])->getQuery(), function ($product) {
            // Skip products that are not properly configured for display
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || !$product->getFirstMediaUrl('images');
        }, $collection->products_per_page ?: 12, 2);
        return response()->json(['collection' => ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'description' => $collection->getTranslatedDescription(), 'display_type' => $collection->display_type, 'products_per_page' => $collection->products_per_page, 'show_filters' => $collection->show_filters], 'products' => $products->items(), 'pagination' => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'per_page' => $products->perPage(), 'total' => $products->total()]]);
    }
    /**
     * Handle productsGallery functionality with proper error handling.
     * @param Collection $collection
     * @param Request $request
     * @return JsonResponse
     */
    public function productsGallery(Collection $collection, Request $request): JsonResponse
    {
        $columnCount = $request->get('columns', 4);
        $columnCount = max(1, min(6, (int) $columnCount));
        // Limit between 1-6 columns
        $products = $collection->products()->published()->with(['images', 'translations'])->get();
        $galleryService = new ProductGalleryService();
        // Apply advanced filtering based on request parameters
        $filters = $request->only(['min_price', 'max_price', 'min_rating', 'has_images', 'is_featured', 'category_id']);
        if (!empty(array_filter($filters))) {
            $products = $galleryService->arrangeWithAdvancedSkipWhile($products, $filters);
        } else {
            // Use basic skipWhile filtering
            $products = $products->skipWhile(function ($product) {
                return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || !$product->getFirstMediaUrl('images');
            });
        }
        $organizedProducts = $galleryService->arrangeForGallery($products, $columnCount);
        return response()->json(['collection' => ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'display_type' => $collection->display_type], 'gallery_layout' => ['columns' => $columnCount, 'total_products' => $products->count(), 'columns_data' => $organizedProducts, 'filters_applied' => $filters]]);
    }
    /**
     * Handle homepageLayout functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function homepageLayout(Request $request): JsonResponse
    {
        $columnCount = $request->get('columns', 4);
        $columnCount = max(2, min(6, (int) $columnCount));
        // Limit between 2-6 columns
        $collections = Collection::withTranslations()->visible()->withCount('products')->orderBy('sort_order')->get()->skipWhile(function ($collection) {
            // Skip collections that are not suitable for homepage display
            return empty($collection->name) || !$collection->is_visible || empty($collection->slug) || $collection->products_count <= 0 || !$collection->getImageUrl('sm');
        });
        $organizedCollections = $collections->splitIn($columnCount);
        return response()->json(['layout' => ['columns' => $columnCount, 'total_collections' => $collections->count(), 'columns_data' => $organizedCollections->map(function ($columnCollections, $columnIndex) {
            return ['column_id' => $columnIndex + 1, 'item_count' => $columnCollections->count(), 'collections' => $columnCollections->map(function ($collection) {
                return ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'description' => $collection->getTranslatedDescription(), 'image_url' => $collection->getImageUrl('sm'), 'products_count' => $collection->products_count, 'display_type' => $collection->display_type];
            })];
        })]]);
    }
    /**
     * Handle personalizedProducts functionality with proper error handling.
     * @param Collection $collection
     * @param Request $request
     * @return JsonResponse
     */
    public function personalizedProducts(Collection $collection, Request $request): JsonResponse
    {
        $products = $collection->products()->published()->with(['images', 'translations', 'brand', 'category'])->get();
        $galleryService = new ProductGalleryService();
        // Apply multiple skipWhile filters based on user preferences and performance
        $userPreferences = $request->only(['preferred_brands', 'preferred_categories', 'excluded_brands', 'excluded_categories', 'price_range']);
        $performanceFilters = $request->only(['min_views', 'min_sales', 'min_rating', 'trending_only']);
        $stockFilters = $request->only(['in_stock_only', 'min_stock']);
        // Apply user preference filtering
        if (!empty(array_filter($userPreferences))) {
            $products = $galleryService->arrangeWithUserPreferences($products, $userPreferences);
        }
        // Apply performance filtering
        if (!empty(array_filter($performanceFilters))) {
            $products = $galleryService->arrangeWithPerformanceFiltering($products, $performanceFilters);
        }
        // Apply stock filtering
        if (!empty(array_filter($stockFilters))) {
            $products = $galleryService->arrangeWithStockFiltering($products, $stockFilters['in_stock_only'] ?? true, $stockFilters['min_stock'] ?? 1);
        }
        // Final quality filtering
        $products = $products->skipWhile(function ($product) {
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || !$product->getFirstMediaUrl('images');
        });
        $columnCount = $request->get('columns', 4);
        $organizedProducts = $galleryService->arrangeForGallery($products, $columnCount);
        return response()->json(['collection' => ['id' => $collection->id, 'name' => $collection->getTranslatedName(), 'slug' => $collection->slug, 'display_type' => $collection->display_type], 'personalized_layout' => ['columns' => $columnCount, 'total_products' => $products->count(), 'columns_data' => $organizedProducts, 'filters_applied' => ['user_preferences' => $userPreferences, 'performance_filters' => $performanceFilters, 'stock_filters' => $stockFilters]]]);
    }
    /**
     * Handle newArrivals functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function newArrivals(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $columnCount = $request->get('columns', 4);
        $collections = Collection::withTranslations()->visible()->withCount('products')->orderBy('sort_order')->get()->skipWhile(function ($collection) {
            return empty($collection->name) || !$collection->is_visible || empty($collection->slug) || $collection->products_count <= 0;
        });
        $galleryService = new ProductGalleryService();
        $allProducts = collect();
        // Use LazyCollection with timeout to prevent long-running new arrivals processing
        $timeout = now()->addSeconds(30);
        // 30 second timeout for new arrivals processing
        LazyCollection::make($collections)->takeUntilTimeout($timeout)->each(function ($collection) use (&$allProducts, $galleryService, $days) {
            $products = $collection->products()->published()->with(['images', 'translations'])->get();
            // Apply date filtering using skipWhile
            $newProducts = $galleryService->arrangeWithDateFiltering($products, ['new_arrivals_days' => $days, 'exclude_old' => true]);
            $allProducts = $allProducts->merge($newProducts);
        });
        // Remove duplicates and apply final filtering
        $uniqueProducts = $allProducts->unique('id')->skipWhile(function ($product) {
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug);
        });
        $organizedProducts = $galleryService->arrangeForGallery($uniqueProducts, $columnCount);
        return response()->json(['new_arrivals' => ['days_filter' => $days, 'columns' => $columnCount, 'total_products' => $uniqueProducts->count(), 'columns_data' => $organizedProducts]]);
    }
}
