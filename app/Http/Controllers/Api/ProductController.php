<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Product\DTOs\GetProductDetailsInputDto;
use App\Application\Product\DTOs\ListCatalogProductsInputDto;
use App\Application\Product\DTOs\SearchProductsInputDto;
use App\Application\Product\Presenters\ProductContractPresenter;
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
    ) {
        // The injected use cases keep the controller thin and testable.
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): JsonResponse|View|Response
    {
        $limit = min(max((int) $request->get('limit', 10), 1), 50);
        $input = new SearchProductsInputDto(
            (string) $request->get('q', ''),
            $limit,
            10,
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
        $currentPage = max(1, (int) $request->get('page', 1));
        $input = new ListCatalogProductsInputDto(
            $perPage,
            $currentPage,
            $request->filled('category') ? (string) $request->get('category') : null,
            $request->filled('brand') ? (string) $request->get('brand') : null,
            (string) $request->get('sort_by', 'name'),
            (string) $request->get('sort_order', 'asc'),
        );

        $result = $this->listCatalogProductsUseCase->execute($input);
        $payload = ProductContractPresenter::fromCatalog($result);

        return $this->respondWithContract($request, $payload);
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

        $payload = ProductContractPresenter::fromDetails($result);

        return $this->respondWithContract($request, $payload);
    }
}
