<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
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

    /**
     * Handle tree functionality with proper error handling.
     */
    public function tree(Request $request): JsonResponse|View|Response
    {
        $categories = Category::query()->where('is_visible', true)->with(['children' => function ($query) {
            $query->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
        }])->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get()->skipWhile(function (Category $category) {
            // Skip categories that are not properly configured
            return empty($category->name) || ! $category->is_visible || empty($category->slug);
        });

        return $this->handleCategoryContentNegotiation($request, $categories);
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
        $data = ['category' => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug, 'description' => $category->description, 'parent' => $category->parent ? ['id' => $category->parent->id, 'name' => $category->parent->name, 'slug' => $category->parent->slug] : null, 'children' => $category->children->map(function ($child) {
            return ['id' => $child->id, 'name' => $child->name, 'slug' => $child->slug, 'description' => $child->description];
        })->toArray(), 'url' => route('category.show', $category->slug), 'product_count' => $category->products_count ?? 0]];

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
