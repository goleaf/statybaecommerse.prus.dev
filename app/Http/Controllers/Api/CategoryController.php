<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Support\Contracts\Entities\CategoryContract;
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
 * CategoryController
 *
 * HTTP controller handling CategoryController related web requests, responses, and business logic with proper validation and error handling.
 */
final class CategoryController extends Controller
{
    use HandlesContentNegotiation;

    public function __construct(private readonly CategoryRepository $categories) {}

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
        $definition = $this->categoryListDefinition();

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = Category::query()
            ->where('is_visible', true)
            // Track the number of products for each category so the API can expose and sort by it.
            ->withCount('products');
        $listQuery->applyFilters($query);
        $listQuery->applySorts($query);

        if (! $listQuery->hasSort('sort_order')) {
            // Ensure a predictable default order when no explicit sort order is provided by the consumer.
            $query->orderBy('sort_order');
        }

        if (! $listQuery->hasSort('name')) {
            // Secondary ordering keeps alphabetic grouping stable for repeated pagination requests.
            $query->orderBy('name');
        }

        $categories = $query->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        $payload = CategoryContract::forCollection($categories, ListResponse::meta($listQuery, $categories));

        return $this->respondWithContract($request, $payload);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Category $category): JsonResponse|View|Response
    {
        $category->load(['children', 'parent']);
        $payload = CategoryContract::forCategory($category);

        return $this->respondWithContract($request, $payload);
    }

    private function categoryListDefinition(): ListQueryDefinition
    {
        return new ListQueryDefinition(
            filters: [
                'search' => [
                    'type' => 'string',
                    // Allow searching the category name and description using a LIKE match.
                    'callback' => static function (Builder $builder, string $term): void {
                        $builder->where(function (Builder $query) use ($term): void {
                            $query->where('name', 'like', "%{$term}%")
                                ->orWhere('description', 'like', "%{$term}%");
                        });
                    },
                ],
            ],
            sortable: [
                // Sort alphabetically by the translated category name when requested.
                'name' => ['column' => 'categories.name'],
                // Maintain the configured manual order for navigation contexts.
                'sort_order' => ['column' => 'categories.sort_order'],
                // Expose product counts so consumers can prioritise fuller categories.
                'product_count' => ['column' => 'products_count'],
            ],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
            defaultPerPage: 20,
            maxPerPage: 100,
        );
    }
}
