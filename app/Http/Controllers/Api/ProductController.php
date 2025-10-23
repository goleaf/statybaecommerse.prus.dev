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
        $products = $this->products->searchVisible($query, $limit);

        $data = ['products' => $products->map(function (Product $product) {
            return ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'sku' => $product->sku, 'price' => $product->price, 'sale_price' => $product->sale_price, 'brand' => $product->brand?->name, 'category' => $product->category?->name, 'image' => $product->getFirstMediaUrl('images', 'thumb'), 'url' => route('product.show', $product->slug), 'stock_quantity' => $product->stock_quantity ?? 0];
        })->toArray(), 'query' => $query, 'total' => $products->count(), 'limit' => $limit];

        $result = $this->searchProductsUseCase->execute(
            new SearchProductsInputDto(
                (string) $request->get('q', ''),
                $limit,
                10,
            )
        );

        $data = $result->toArray();
        $data['products'] = array_map(
            static fn (array $product) => $product + ['url' => route('product.show', $product['slug'])],
            $data['products'],
        );

        $result = $this->searchProductsUseCase->execute($input);
        $payload = ProductContractPresenter::fromSearch($result);

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

        $listQuery = ListQueryValidator::fromRequest($request, $definition);
        $filters = $listQuery->filters;

        $query = Product::query()->where('is_visible', true)->with(['brand', 'media', 'category']);

        if (array_key_exists('category', $filters)) {
            $query->whereHas('category', static function ($q) use ($filters): void {
                $q->where('slug', $filters['category']);
            });
        }

        if (array_key_exists('brand', $filters)) {
            $query->whereHas('brand', static function ($q) use ($filters): void {
                $q->where('slug', $filters['brand']);
            });
        }

        $query = $listQuery->apply($query, $definition);

        $products = $query->get()->filter(static function (Product $product) {
            return ! (empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug));
        })->values();

        $total = $products->count();
        $offset = ($listQuery->page - 1) * $listQuery->perPage;
        $paginatedProducts = $products->slice($offset, $listQuery->perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedProducts,
            $total,
            $listQuery->perPage,
            $listQuery->page,
            ['path' => $request->url(), 'pageName' => 'page'],
        );

        $paginator->appends($request->query());

        if ($request->expectsJson()) {
            $response = ListResponse::fromPaginator(
                $paginator->through(static function (Product $product) {
                    return [
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
                    ];
                }),
            );

            return response()->json($response);
        }

        return $this->handleProductContentNegotiation($request, $paginator);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, string $slug): JsonResponse|View|Response
    {
        $resolved = $this->products->findVisibleBySlug($product->slug) ?? $product->loadMissing(['brand', 'media', 'categories', 'variants']);
        $data = ['product' => ['id' => $resolved->id, 'name' => $resolved->name, 'slug' => $resolved->slug, 'sku' => $resolved->sku, 'description' => $resolved->description, 'price' => $resolved->price, 'sale_price' => $resolved->sale_price, 'brand' => $resolved->brand?->name, 'category' => $resolved->category?->name, 'images' => $resolved->getMedia('images')->map(function ($media) {
            return ['url' => $media->getUrl(), 'thumb' => $media->getUrl('thumb'), 'alt' => $media->getCustomProperty('alt', '')];
        })->toArray(), 'variants' => $resolved->variants->map(function ($variant) {
            return ['id' => $variant->id, 'name' => $variant->name, 'sku' => $variant->sku, 'price' => $variant->price, 'stock_quantity' => $variant->stock_quantity];
        })->toArray(), 'stock_quantity' => $resolved->stock_quantity ?? 0, 'is_visible' => $resolved->is_visible, 'url' => route('product.show', $resolved->slug)]];

        $data = $result->toArray();
        $data['product']['url'] = route('product.show', $data['product']['slug']);

        $payload = ProductContractPresenter::fromDetails($result);

        return $this->respondWithContract($request, $payload);
    }
}
