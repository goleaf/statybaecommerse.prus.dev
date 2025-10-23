<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Product\DTOs\GetProductDetailsInputDto;
use App\Application\Product\DTOs\ListCatalogProductsInputDto;
use App\Application\Product\DTOs\SearchProductsInputDto;
use App\Application\Product\UseCases\GetProductDetailsUseCase;
use App\Application\Product\UseCases\ListCatalogProductsUseCase;
use App\Application\Product\UseCases\SearchProductsUseCase;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Contracts\Entities\ProductContract;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * ProductController
 *
 * HTTP controller handling ProductController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductController extends Controller
{
    use HandlesContentNegotiation;

    public function __construct(private readonly ProductRepository $products)
    {
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): JsonResponse|View|Response
    {
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
        $definition = ListQueryDefinition::make(
            allowedSorts: [
                'name' => 'name',
                'price' => 'price',
                'sale_price' => 'sale_price',
                'created_at' => 'created_at',
            ],
            defaultSort: 'name',
            defaultDirection: 'asc',
            defaultPerPage: 20,
            maxPerPage: 100,
        );

        $payload = ProductContract::forCollection($paginatedData, [
            'total' => $total,
            'limit' => $perPage,
        ]);

        return $this->respondWithContract($request, $payload);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, string $slug): JsonResponse|View|Response
    {
        $product->load(['brand', 'media', 'category', 'variants']);
        $payload = ProductContract::forProduct($product);

        return $this->respondWithContract($request, $payload);
    }
}
