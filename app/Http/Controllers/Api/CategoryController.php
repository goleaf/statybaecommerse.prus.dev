<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Contracts\Entities\CategoryContract;
use App\Traits\HandlesContentNegotiation;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
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
        $definition = $this->categoryListDefinition();
        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = Category::query()
            ->where('is_visible', true)
            ->withCount('products');

        $paginator = $listQuery->apply($query, $definition);

        $response = ListResponse::fromPaginator(
            $paginator,
            $listQuery,
            static fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'sort_order' => $category->sort_order,
                'is_visible' => (bool) $category->is_visible,
                'product_count' => $category->products_count ?? 0,
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

        return $this->handleCategoryContentNegotiation(
            $request,
            $paginator->getCollection(),
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
    public function show(Request $request, Category $category): JsonResponse|View|Response
    {
        $category->load(['children', 'parent']);
        $payload = CategoryContract::forCategory($category);

        return $this->handleContentNegotiation($request, $data);
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
