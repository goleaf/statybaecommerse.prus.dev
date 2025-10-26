<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Support\Contracts\Entities\CategoryContract;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * CategoryController
 *
 * HTTP controller handling CategoryController related web requests, responses, and business logic with proper validation and error handling.
 */
final class CategoryController extends Controller
{
    use HandlesContentNegotiation;

    public function __construct(private readonly CategoryRepository $categories)
    {
    }

    /**
     * Handle tree functionality with proper error handling.
     */
    public function tree(Request $request): JsonResponse|View|Response
    {
        $categories = $this->categories->getVisibleTree();

        $payload = CategoryContract::forCollection($categories, ['context' => 'tree']);

        return $this->respondWithContract($request, $payload);
    }

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): JsonResponse|View|Response
    {
        $definition = new ListQueryDefinition(
            filters: [
                'search' => [
                    'type' => 'string',
                    'callback' => static function (Builder $builder, string $term): void {
                        $builder->where(function (Builder $query) use ($term): void {
                            $query->where('name', 'like', "%{$term}%")
                                ->orWhere('description', 'like', "%{$term}%");
                        });
                    },
                ],
            ],
            sortable: [
                'name' => ['column' => 'categories.name'],
                'sort_order' => ['column' => 'categories.sort_order'],
            ],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
            defaultPerPage: 20,
            maxPerPage: 100,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = Category::query()->where('is_visible', true)->withCount('products');
        $listQuery->applyFilters($query);
        $listQuery->applySorts($query);

        if (! $listQuery->hasSort('sort_order')) {
            $query->orderBy('sort_order');
        }

        if (! $listQuery->hasSort('name')) {
            $query->orderBy('name');
        }

        $categories = $query->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        $payload = CategoryContract::forCollection($categories, ListResponse::meta($listQuery, $categories));

        return $this->respondWithContract($request, $payload);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Category $category)
    {
        // Retrieve the incoming HTTP request instance so we can validate query parameters safely.
        $request = request();

        // Ensure hidden or disabled categories respond with a 404 even if global scopes are bypassed elsewhere.
        if (! $category->isVisible() || ! $category->isActive()) {
            abort(404);
        }

        // Validate pagination and sorting parameters so invalid values return a 422 response instead of silently failing.
        $validated = validator($request->query(), [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'children_page' => ['sometimes', 'integer', 'min:1'],
            'children_per_page' => ['sometimes', 'integer', 'min:1', 'max:25'],
            'sort' => ['sometimes', 'string', function (string $attribute, string $value, $fail): void {
                // Strip the optional descending indicator and confirm the sort key is in our approved list.
                $key = ltrim($value, '-');
                $allowedSorts = ['name', 'price', 'latest', 'featured'];

                if (! in_array($key, $allowedSorts, true)) {
                    $fail('The selected sort option is invalid.');
                }
            }],
        ])->validate();

        // Require authorization when the category is marked as private so restricted catalogues stay protected.
        if ((bool) $category->getAttribute('is_private')) {
            $this->authorize('view', $category);
        }

        // Determine pagination inputs with sane defaults that respect the configured maximums.
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 12);
        $childrenPage = (int) ($validated['children_page'] ?? 1);
        $childrenPerPage = (int) ($validated['children_per_page'] ?? 10);

        // Resolve the active sorting strategy for the product catalogue listing.
        $sortParameter = (string) ($validated['sort'] ?? 'featured');
        $sortKey = ltrim($sortParameter, '-');
        $sortDirection = str_starts_with($sortParameter, '-') ? 'desc' : 'asc';

        // Preload the immediate relationships needed for breadcrumb construction and navigation widgets.
        $category->loadMissing(['parent']);

        // Paginate children with a limited depth eager load so large trees never trigger N+1 queries.
        $childrenPaginator = $category->children()
            ->visible()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['children' => static function ($query): void {
                // Limit nested children to avoid returning excessively deep trees.
                $query->visible()->orderBy('sort_order')->orderBy('name')->limit(8);
            }])
            ->paginate($childrenPerPage, ['*'], 'children_page', $childrenPage);

        // Eager load published products with their media and brand data to avoid N+1 queries when serialising.
        $productQuery = $category->products()
            ->published()
            ->with(['brand', 'media'])
            ->withPivot('category_id');

        // Apply the selected sorting rules while keeping the default order deterministic.
        match ($sortKey) {
            'name' => $productQuery->orderBy('products.name', $sortDirection),
            'price' => $productQuery->orderBy('products.price', $sortDirection),
            'latest' => $productQuery->orderBy('products.published_at', 'desc')->orderBy('products.created_at', 'desc'),
            default => $productQuery->orderByDesc('products.is_featured')->orderBy('products.published_at', 'desc'),
        };

        // Execute the paginated query for category products with capped page sizes.
        $productsPaginator = $productQuery->paginate($perPage, ['products.*'], 'page', $page);

        // Fetch a short curated list of featured products for the category landing page hero content.
        $featuredProducts = $category->products()
            ->published()
            ->where('products.is_featured', true)
            ->with(['brand', 'media'])
            ->orderByDesc('products.published_at')
            ->limit(8)
            ->get();

        // Attach the paginated collections to the category so JSON resources can leverage whenLoaded checks.
        $category->setRelation('children', $childrenPaginator->getCollection());
        $category->setRelation('childrenPagination', $childrenPaginator);
        $category->setRelation('products', $productsPaginator->getCollection());
        $category->setRelation('productsPagination', $productsPaginator);
        $category->setRelation('featuredProducts', $featuredProducts);

        // Transform the payload using dedicated API resources for a consistent consumer-facing schema.
        return (new CategoryResource($category))
            ->additional([
                'meta' => [
                    'products' => $this->paginationMeta($productsPaginator),
                    'children' => $this->paginationMeta($childrenPaginator),
                ],
            ]);
    }

    /**
     * Build a pagination metadata array for consistent API responses.
     */
    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        // Provide a lightweight pagination structure that mirrors Laravel's paginator output.
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    private function categoryListDefinition(): ListQueryDefinition
    {
        return ListQueryDefinition::make()
            ->defaultPerPage(20)
            ->maxPerPage(100)
            ->defaultSort('sort_order', 'asc')
            ->allowedSorts([
                'name' => ['column' => ['name', 'id']],
                'sort_order' => ['column' => ['sort_order', 'name']],
                'product_count' => ['column' => 'products_count'],
            ])
            ->filters([
                'search' => [
                    'type' => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $search): void {
                        $builder->where(static function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%'.$search.'%')
                                ->orWhere('description', 'like', '%'.$search.'%');
                        });
                    },
                ],
            ]);
    }
}
