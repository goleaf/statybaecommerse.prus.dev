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

        return $this->handleContentNegotiation($request, $data);
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
        $currentPage = max(1, (int) $request->get('page', 1));

        $result = $this->listCatalogProductsUseCase->execute(
            new ListCatalogProductsInputDto(
                $perPage,
                $currentPage,
                $category ? (string) $category : null,
                $brand ? (string) $brand : null,
                (string) $sortBy,
                (string) $sortOrder,
            )
        );

        $data = $result->toArray();
        $data['products'] = array_map(
            static fn (array $product) => $product + ['url' => route('product.show', $product['slug'])],
            $data['products'],
        );

        return $this->handleContentNegotiation($request, $data);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, string $slug): JsonResponse|View|Response
    {
        try {
            $result = $this->getProductDetailsUseCase->execute(new GetProductDetailsInputDto($slug));
        } catch (ProductNotFoundException $exception) {
            abort(404, $exception->getMessage());
        }

        $data = $result->toArray();
        $data['product']['url'] = route('product.show', $data['product']['slug']);

        return $this->handleContentNegotiation($request, $data);
    }
}
