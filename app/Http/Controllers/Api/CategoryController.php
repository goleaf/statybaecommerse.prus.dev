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

        $payload = CategoryContract::forCollection($categories);

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
}
