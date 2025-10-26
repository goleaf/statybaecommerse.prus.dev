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
        $definition = new ListQueryDefinition(
            filters: [
                'search' => [
                    'type'     => 'string',
                    'callback' => static function (Builder $builder, string $term): void {
                        $builder->where(function (Builder $query) use ($term): void {
                            $query->where('name', 'like', "%{$term}%")
                                ->orWhere('description', 'like', "%{$term}%");
                        });
                    },
                ],
            ],
            sortable: [
                'name'       => ['column' => 'categories.name'],
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
    public function show(Request $request, Category $category): JsonResponse|View|Response
    {
        $category->load(['children', 'parent']);
        $payload = CategoryContract::forCategory($category);

        return $this->respondWithContract($request, $payload);
    }

    private function categoryListDefinition(): ListQueryDefinition
    {
        return ListQueryDefinition::make()
            ->defaultPerPage(20)
            ->maxPerPage(100)
            ->defaultSort('sort_order', 'asc')
            ->allowedSorts([
                'name'          => ['column' => ['name', 'id']],
                'sort_order'    => ['column' => ['sort_order', 'name']],
                'product_count' => ['column' => 'products_count'],
            ])
            ->filters([
                'search' => [
                    'type'     => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $search): void {
                        $builder->where(static function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%' . $search . '%')
                                ->orWhere('description', 'like', '%' . $search . '%');
                        });
                    },
                ],
            ]);
    }
}
