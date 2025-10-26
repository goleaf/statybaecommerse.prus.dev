<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Product\DTOs\SearchProductsInputDto;
use App\Application\Product\Presenters\ProductContractPresenter;
use App\Application\Product\UseCases\SearchProductsUseCase;
use App\Enums\Api\ProductSort;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
    ) {
        // Keep the search use case injectable for backwards-compatible endpoints.
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
     * Provide a hardened product index with validated filters and eager loading.
     */
    public function index(ProductIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();

        // Construct the base query with all relations required by the resource to avoid N+1 issues.
        $query = Product::query()
            ->with(['media', 'variants', 'categories', 'brand'])
            ->withCount('reviews');

        // Apply an allow-listed search term if supplied by the caller.
        if (is_string($filters['q']) && $filters['q'] !== '') {
            $query->searchTerm($filters['q']);
        }

        // Restrict to a specific category slug when provided.
        if (is_string($filters['category']) && $filters['category'] !== '') {
            $query->whereHas('categories', static function ($builder) use ($filters): void {
                $builder->where('slug', $filters['category']);
            });
        }

        // Guard against unrealistic range queries by clamping against validated numeric bounds.
        if ($filters['price_min'] !== null) {
            $query->where('price', '>=', (float) $filters['price_min']);
        }

        if ($filters['price_max'] !== null) {
            $query->where('price', '<=', (float) $filters['price_max']);
        }

        // Apply deterministic ordering via the enum-backed allow-list.
        $this->applySort($query, ProductSort::from($filters['sort']));

        $products = $query->paginate(
            (int) $filters['per_page'],
            ['*'],
            'page',
            (int) $filters['page'],
        );

        // Surface the filters alongside pagination metadata for transparent caching and diagnostics.
        $resource = ProductResource::collection($products)->additional([
            'contract' => 'product-resource',
            'version'  => 'v2',
            'meta'     => [
                'generated_at' => now()->toISOString(),
                'filters'      => [
                    'q'         => $filters['q'],
                    'category'  => $filters['category'],
                    'price_min' => $filters['price_min'],
                    'price_max' => $filters['price_max'],
                    'sort'      => $filters['sort'],
                    'per_page'  => $filters['per_page'],
                    'page'      => $filters['page'],
                ],
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                    'last_page'    => $products->lastPage(),
                ],
            ],
        ]);

        return $resource->response();
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        // Always ensure the product payload includes the relations our resource expects.
        $product->loadMissing(['media', 'variants', 'categories', 'brand'])->loadCount('reviews');

        // Abort for soft-deleted or unpublished products to prevent leaking draft catalogue data.
        if ($product->trashed() || ! $product->isPublished()) {
            abort(404);
        }

        // For private listings require explicit authorisation before exposing details.
        if (! $product->is_visible && (! $request->user() || ! $request->user()->can('view', $product))) {
            abort(404);
        }

        $etagPayload = implode('|', [
            $product->getKey(),
            optional($product->updated_at)?->toIsoString(),
            (string) $product->reviews_count,
        ]);
        $etag = sha1($etagPayload);

        $resource = (new ProductResource($product))->additional([
            'contract' => 'product-resource',
            'version'  => 'v2',
            'meta'     => [
                'generated_at' => now()->toISOString(),
                'etag'         => $etag,
            ],
        ]);

        /** @var JsonResponse $response */
        $response = $resource->response();
        $response->setEtag($etag);
        $response->setStatusCode(SymfonyResponse::HTTP_OK);
        $response->headers->set('Cache-Control', 'public, max-age=60, must-revalidate');

        if ($product->updated_at !== null) {
            $response->setLastModified($product->updated_at);
        }

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }

    /**
     * Map ProductSort enum values to concrete order clauses.
     */
    private function applySort($query, ProductSort $sort): void
    {
        $query->when(true, static function ($builder) use ($sort): void {
            switch ($sort) {
                case ProductSort::NAME_DESC:
                    $builder->orderByDesc('name');
                    break;
                case ProductSort::PRICE_ASC:
                    $builder->orderBy('price');
                    break;
                case ProductSort::PRICE_DESC:
                    $builder->orderByDesc('price');
                    break;
                case ProductSort::NEWEST:
                    $builder->orderByDesc('published_at');
                    break;
                default:
                    $builder->orderBy('name');
                    break;
            }

            // Always include a deterministic tie-breaker for pagination stability.
            $builder->orderByDesc('id');
        });
    }
}
