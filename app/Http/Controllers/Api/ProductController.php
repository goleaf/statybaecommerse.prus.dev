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
        try {
            $result = $this->getProductDetailsUseCase->execute(new GetProductDetailsInputDto($slug));
        } catch (ProductNotFoundException $exception) {
            abort(404, $exception->getMessage());
        }

        $payload = ProductContractPresenter::fromDetails($result);

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
