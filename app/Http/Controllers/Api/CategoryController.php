<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Traits\HandlesContentNegotiation;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
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
        $definition = ListQueryDefinition::make(
            allowedSorts: [
                'sort_order' => 'sort_order',
                'name' => 'name',
                'created_at' => 'created_at',
            ],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
            defaultPerPage: 20,
            maxPerPage: 100,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = Category::query()
            ->where('is_visible', true)
            ->withCount('products');

        if ($search = $listQuery->filter('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $query = $listQuery->apply($query, $definition);

        if ($listQuery->sortField === 'sort_order') {
            $query->orderBy('name');
        }

        $paginator = $query->paginate($listQuery->perPage, ['*'], 'page', $listQuery->page)
            ->appends($request->query());

        if ($request->expectsJson()) {
            $response = ListResponse::fromPaginator(
                $paginator->through(static function (Category $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'product_count' => $category->products_count,
                        'url' => route('category.show', $category->slug),
                    ];
                }),
            );

            return response()->json($response);
        }

        return $this->handleCategoryContentNegotiation($request, $paginator);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Category $category): JsonResponse|View|Response
    {
        $category = $this->categories->loadForShow($category);
        $data = ['category' => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug, 'description' => $category->description, 'parent' => $category->parent ? ['id' => $category->parent->id, 'name' => $category->parent->name, 'slug' => $category->parent->slug] : null, 'children' => $category->children->map(function ($child) {
            return ['id' => $child->id, 'name' => $child->name, 'slug' => $child->slug, 'description' => $child->description];
        })->toArray(), 'url' => route('category.show', $category->slug), 'product_count' => $category->products_count ?? 0]];

        return $this->respondWithContract($request, $payload);
    }
}
