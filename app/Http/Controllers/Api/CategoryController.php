<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Contracts\Entities\CategoryContract;
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
    public function show(Request $request, Category $category): JsonResponse|View|Response
    {
        $category->load(['children', 'parent']);
        $data = ['category' => CategoryContract::fromModel($category)];

        return $this->respondWithContract($request, $payload);
    }
}
