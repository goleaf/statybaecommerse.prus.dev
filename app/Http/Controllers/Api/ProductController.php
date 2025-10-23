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

    public function __construct(
        private readonly SearchProductsUseCase $searchProductsUseCase,
        private readonly ListCatalogProductsUseCase $listCatalogProductsUseCase,
        private readonly GetProductDetailsUseCase $getProductDetailsUseCase,
    ) {}

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
        })->with(['brand', 'media', 'categories'])->cursor()->takeUntilTimeout($timeout)->take($limit)->collect();
        // Apply skipWhile to filter out products that are not properly configured
        $filteredProducts = $products->skipWhile(function (Product $product) {
            return empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug);
        });
        $data = [
            'products' => $filteredProducts->map(static fn (Product $product): array => ProductContract::fromModel($product))->toArray(),
            'query' => $query,
            'total' => $filteredProducts->count(),
            'limit' => $limit,
        ];

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
        $perPage = max(1, min((int) $request->get('per_page', 20), 100));
        $category = $request->get('category');
        $brand = $request->get('brand');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query = Product::query()->where('is_visible', true)->with(['brand', 'media', 'categories']);
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }
        if ($brand) {
            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where('slug', $brand);
            });
        }
        $products = $query->orderBy($sortBy, $sortOrder)->get()->skipWhile(function (Product $product) {
            // Skip products that are not properly configured for catalog display
            return empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug);
        });
        // Apply pagination manually after skipWhile filtering
        $total = $products->count();
        $currentPage = (int) $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedProducts = $products->slice($offset, $perPage);
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator($paginatedProducts, $total, $perPage, $currentPage, ['path' => $request->url(), 'pageName' => 'page']);

        $paginatedData->setCollection($paginatedData->getCollection()->map(static fn (Product $product): array => ProductContract::fromModel($product)));

        return $this->handleProductContentNegotiation($request, $paginatedData);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, string $slug): JsonResponse|View|Response
    {
        $product->load(['brand', 'media', 'categories']);
        $data = ['product' => ProductContract::fromModel($product)];

        $data = $result->toArray();
        $data['product']['url'] = route('product.show', $data['product']['slug']);

        $payload = ProductContractPresenter::fromDetails($result);

        return $this->respondWithContract($request, $payload);
    }
}
