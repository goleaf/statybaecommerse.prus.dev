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
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Database\Eloquent\Builder;
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
        $definition = $this->productCatalogDefinition();
        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = Product::query()
            ->where('is_visible', true)
            ->where('price', '>', 0)
            ->whereNotNull('slug')
            ->where('slug', '<>', '')
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->with(['brand', 'media', 'category']);

        $paginator = $listQuery->apply($query, $definition);

        $response = ListResponse::fromPaginator(
            $paginator,
            $listQuery,
            static fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'image' => $product->getFirstMediaUrl('images', 'thumb'),
                'url' => route('product.show', $product->slug),
                'stock_quantity' => $product->stock_quantity ?? 0,
                'is_visible' => (bool) $product->is_visible,
            ],
        );

        if ($request->accepts(['application/json', 'text/json'])) {
            return response()->json([
                'success' => true,
                'data' => $response['data'],
                'meta' => $response['meta'],
                'links' => $response['links'],
            ]);
        }

        return $this->handleProductContentNegotiation(
            $request,
            $paginator,
            null,
            [
                'pagination' => $response['meta']['pagination'],
                'sorting' => $response['meta']['sort'],
                'filters' => $response['meta']['filters'],
                'links' => $response['links'],
            ],
        );
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

    private function productCatalogDefinition(): ListQueryDefinition
    {
        return ListQueryDefinition::make()
            ->defaultPerPage(20)
            ->maxPerPage(100)
            ->defaultSort('name', 'asc')
            ->allowedSorts([
                'name' => ['column' => ['name', 'id']],
                'price' => ['column' => 'price'],
                'sale_price' => ['column' => 'sale_price'],
                'created_at' => ['column' => 'created_at'],
            ])
            ->filters([
                'category' => [
                    'type' => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $slug): void {
                        $builder->whereHas('category', static function (Builder $query) use ($slug): void {
                            $query->where('slug', $slug);
                        });
                    },
                ],
                'brand' => [
                    'type' => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $slug): void {
                        $builder->whereHas('brand', static function (Builder $query) use ($slug): void {
                            $query->where('slug', $slug);
                        });
                    },
                ],
            ]);
    }
}
