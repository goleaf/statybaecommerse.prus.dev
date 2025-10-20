<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Traits\HandlesContentNegotiation;
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

        return $this->handleCategoryContentNegotiation($request, $categories);
    }

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): JsonResponse|View|Response
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $search = $request->get('search');
        $categories = $this->categories->paginateVisible(['search' => $search], $perPage);

        return $this->handleCategoryContentNegotiation($request, $categories);
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

        return $this->handleContentNegotiation($request, $data);
    }
}
