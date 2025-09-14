<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use App\Services\ProductGalleryService;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * CollectionController
 * 
 * HTTP controller handling web requests and responses.
 */
class CollectionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Collection::withTranslations()
            ->visible()
            ->ordered();

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
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $collections = PaginationService::paginateWithContext($query, 'collections');

        return view('collections.index', compact('collections'));
    }

    public function show(Collection $collection): View
    {
        $collection->load(['products' => function ($query) {
            $query->published()->with(['images', 'translations']);
        }]);

        $relatedCollections = Collection::withTranslations()
            ->visible()
            ->where('id', '!=', $collection->id)
            ->where('display_type', $collection->display_type)
            ->limit(4)
            ->get()
            ->skipWhile(function ($relatedCollection) {
                // Skip related collections that are not properly configured
                return empty($relatedCollection->name) || 
                       !$relatedCollection->is_visible ||
                       empty($relatedCollection->slug) ||
                       $relatedCollection->products()->count() <= 0;
            });

        // Use splitIn method for better product organization
        $galleryService = new ProductGalleryService();
        $organizedProducts = $galleryService->arrangeForCollection(
            $collection->products, 
            $collection->display_type ?? 1
        );

        return view('collections.show', compact('collection', 'relatedCollections', 'organizedProducts'));
    }

    // API Endpoints
    public function api(Request $request): JsonResponse
    {
        $query = Collection::withTranslations()->visible();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $collections = $query->limit(20)->get();

        return response()->json([
            'collections' => $collections
                ->skipWhile(function ($collection) {
                    // Skip collections that are not properly configured or have missing essential data
                    return empty($collection->name) || 
                           !$collection->is_visible ||
                           empty($collection->slug);
                })
                ->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->getTranslatedName(),
                        'slug' => $collection->slug,
                        'description' => $collection->getTranslatedDescription(),
                        'image_url' => $collection->getImageUrl(),
                        'products_count' => $collection->getProductsCountAttribute(),
                        'display_type' => $collection->display_type,
                        'is_automatic' => $collection->is_automatic,
                    ];
                }),
        ]);
    }

    public function byType(string $type): JsonResponse
    {
        $isAutomatic = $type === 'automatic';
        
        $collections = Collection::withTranslations()
            ->visible()
            ->where('is_automatic', $isAutomatic)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'collections' => $collections
                ->skipWhile(function ($collection) {
                    // Skip collections that are not properly configured or have missing essential data
                    return empty($collection->name) || 
                           !$collection->is_visible ||
                           empty($collection->slug);
                })
                ->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->getTranslatedName(),
                        'slug' => $collection->slug,
                        'description' => $collection->getTranslatedDescription(),
                        'image_url' => $collection->getImageUrl(),
                        'products_count' => $collection->getProductsCountAttribute(),
                        'display_type' => $collection->display_type,
                    ];
                }),
        ]);
    }

    public function withProducts(): JsonResponse
    {
        $collections = Collection::withTranslations()
            ->visible()
            ->has('products')
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'collections' => $collections
                ->skipWhile(function ($collection) {
                    // Skip collections that are not properly configured or have missing essential data
                    return empty($collection->name) || 
                           !$collection->is_visible ||
                           empty($collection->slug) ||
                           $collection->products_count <= 0;
                })
                ->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->getTranslatedName(),
                        'slug' => $collection->slug,
                        'description' => $collection->getTranslatedDescription(),
                        'image_url' => $collection->getImageUrl(),
                        'products_count' => $collection->products_count,
                        'display_type' => $collection->display_type,
                        'is_automatic' => $collection->is_automatic,
                    ];
                }),
        ]);
    }

    public function popular(): JsonResponse
    {
        $collections = Collection::withTranslations()
            ->visible()
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'collections' => $collections
                ->skipWhile(function ($collection) {
                    // Skip collections that are not properly configured or have missing essential data
                    return empty($collection->name) || 
                           !$collection->is_visible ||
                           empty($collection->slug) ||
                           $collection->products_count <= 0;
                })
                ->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->getTranslatedName(),
                        'slug' => $collection->slug,
                        'description' => $collection->getTranslatedDescription(),
                        'image_url' => $collection->getImageUrl('sm'),
                        'products_count' => $collection->products_count,
                        'display_type' => $collection->display_type,
                    ];
                }),
        ]);
    }

    public function statistics(): JsonResponse
    {
        $totalCollections = Collection::count();
        $visibleCollections = Collection::visible()->count();
        $automaticCollections = Collection::where('is_automatic', true)->count();
        $manualCollections = Collection::where('is_automatic', false)->count();
        $collectionsWithProducts = Collection::has('products')->count();

        return response()->json([
            'total_collections' => $totalCollections,
            'visible_collections' => $visibleCollections,
            'automatic_collections' => $automaticCollections,
            'manual_collections' => $manualCollections,
            'collections_with_products' => $collectionsWithProducts,
            'collections_without_products' => $totalCollections - $collectionsWithProducts,
        ]);
    }

    public function products(Collection $collection): JsonResponse
    {
        $products = PaginationService::paginateQueryWithSkipWhile(
            $collection->products()
                ->published()
                ->with(['images', 'translations']),
            function ($product) {
                // Skip products that are not properly configured for display
                return empty($product->name) || 
                       !$product->is_visible ||
                       $product->price <= 0 ||
                       empty($product->slug) ||
                       !$product->getFirstMediaUrl('images');
            },
            $collection->products_per_page ?: 12,
            2
        );

        return response()->json([
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->getTranslatedName(),
                'slug' => $collection->slug,
                'description' => $collection->getTranslatedDescription(),
                'display_type' => $collection->display_type,
                'products_per_page' => $collection->products_per_page,
                'show_filters' => $collection->show_filters,
            ],
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get products organized using splitIn method for gallery layout
     */
    public function productsGallery(Collection $collection, Request $request): JsonResponse
    {
        $columnCount = $request->get('columns', 4);
        $columnCount = max(1, min(6, (int) $columnCount)); // Limit between 1-6 columns

        $products = $collection->products()
            ->published()
            ->with(['images', 'translations'])
            ->get()
            ->skipWhile(function ($product) {
                // Skip products that are not properly configured for gallery display
                return empty($product->name) || 
                       !$product->is_visible ||
                       $product->price <= 0 ||
                       empty($product->slug) ||
                       !$product->getFirstMediaUrl('images');
            });

        $galleryService = new ProductGalleryService();
        $organizedProducts = $galleryService->arrangeForGallery($products, $columnCount);

        return response()->json([
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->getTranslatedName(),
                'slug' => $collection->slug,
                'display_type' => $collection->display_type,
            ],
            'gallery_layout' => [
                'columns' => $columnCount,
                'total_products' => $products->count(),
                'columns_data' => $organizedProducts,
            ],
        ]);
    }

    /**
     * Get collections organized using splitIn method for homepage display
     */
    public function homepageLayout(Request $request): JsonResponse
    {
        $columnCount = $request->get('columns', 4);
        $columnCount = max(2, min(6, (int) $columnCount)); // Limit between 2-6 columns

        $collections = Collection::withTranslations()
            ->visible()
            ->withCount('products')
            ->orderBy('sort_order')
            ->get()
            ->skipWhile(function ($collection) {
                // Skip collections that are not suitable for homepage display
                return empty($collection->name) || 
                       !$collection->is_visible ||
                       empty($collection->slug) ||
                       $collection->products_count <= 0 ||
                       !$collection->getImageUrl('sm');
            });

        $organizedCollections = $collections->splitIn($columnCount);

        return response()->json([
            'layout' => [
                'columns' => $columnCount,
                'total_collections' => $collections->count(),
                'columns_data' => $organizedCollections->map(function ($columnCollections, $columnIndex) {
                    return [
                        'column_id' => $columnIndex + 1,
                        'item_count' => $columnCollections->count(),
                        'collections' => $columnCollections->map(function ($collection) {
                            return [
                                'id' => $collection->id,
                                'name' => $collection->getTranslatedName(),
                                'slug' => $collection->slug,
                                'description' => $collection->getTranslatedDescription(),
                                'image_url' => $collection->getImageUrl('sm'),
                                'products_count' => $collection->products_count,
                                'display_type' => $collection->display_type,
                            ];
                        })
                    ];
                })
            ],
        ]);
    }
}