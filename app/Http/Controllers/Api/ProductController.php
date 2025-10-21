<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Contracts\Entities\ProductContract;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/**
 * ProductController
 *
 * HTTP controller handling ProductController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductController extends Controller
{
    use HandlesContentNegotiation;

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): JsonResponse|View|Response
    {
        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 10), 50);
        // Use LazyCollection with timeout to prevent long-running search operations
        $timeout = now()->addSeconds(10);
        // 10 second timeout for product search
        $products = Product::query()->where('is_visible', true)->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")->orWhere('description', 'like', "%{$query}%")->orWhere('sku', 'like', "%{$query}%");
        })->with(['brand', 'media', 'category'])->cursor()->takeUntilTimeout($timeout)->take($limit)->collect();
        // Apply skipWhile to filter out products that are not properly configured
        $filteredProducts = $products->skipWhile(function (Product $product) {
            return empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug);
        });
        $payload = ProductContract::forCollection($filteredProducts, [
            'query' => $query,
            'total' => $filteredProducts->count(),
            'limit' => $limit,
        ]);

        return $this->respondWithContract($request, $payload);
    }

    /**
     * Handle catalog functionality with proper error handling.
     */
    public function catalog(Request $request): JsonResponse|View|Response
    {
        $definition = new ListQueryDefinition(
            filters: [
                'category' => [
                    'type' => 'string',
                    'callback' => static function (Builder $builder, string $slug): void {
                        $builder->whereHas('category', static function (Builder $query) use ($slug): void {
                            $query->where('slug', $slug);
                        });
                    },
                ],
                'brand' => [
                    'type' => 'string',
                    'callback' => static function (Builder $builder, string $slug): void {
                        $builder->whereHas('brand', static function (Builder $query) use ($slug): void {
                            $query->where('slug', $slug);
                        });
                    },
                ],
            ],
            sortable: [
                'name' => ['column' => 'products.name'],
                'price' => ['column' => 'products.price'],
                'created_at' => ['column' => 'products.created_at'],
            ],
            defaultSort: 'name',
            defaultDirection: 'asc',
            defaultPerPage: 20,
            maxPerPage: 100,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = Product::query()->where('is_visible', true)->with(['brand', 'media', 'category']);
        $listQuery->applyFilters($query);
        $listQuery->applySorts($query);

        if (! $listQuery->hasSort('name')) {
            $query->orderBy('products.name');
        }

        $products = $query->get()->skipWhile(function (Product $product) {
            // Skip products that are not properly configured for catalog display
            return empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug);
        });
        // Apply pagination manually after skipWhile filtering
        $total = $products->count();
        $currentPage = $listQuery->page();
        $perPage = $listQuery->perPage();
        $offset = ($currentPage - 1) * $perPage;
        $paginatedProducts = $products->slice($offset, $perPage)->values();
        $paginatedData = new LengthAwarePaginator($paginatedProducts, $total, $perPage, $currentPage, ['path' => $request->url(), 'pageName' => 'page']);

        $payload = ProductContract::forCollection($paginatedData, ListResponse::meta($listQuery, $paginatedData, [
            'total' => $total,
            'limit' => $perPage,
        ]));

        return $this->respondWithContract($request, $payload);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Product $product): JsonResponse|View|Response
    {
        $product->load(['brand', 'media', 'category', 'variants']);
        $payload = ProductContract::forProduct($product);

        return $this->respondWithContract($request, $payload);
    }
}
