<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final /**
 * CategoryController
 * 
 * HTTP controller handling web requests and responses with content negotiation.
 */
class CategoryController extends Controller
{
    use HandlesContentNegotiation;

    public function tree(Request $request): JsonResponse|View|Response
    {
        $categories = Category::query()
            ->where('is_visible', true)
            ->with(['children' => function ($query) {
                $query
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->handleCategoryContentNegotiation($request, $categories);
    }

    public function index(Request $request): JsonResponse|View|Response
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $search = $request->get('search');

        $query = Category::query()
            ->where('is_visible', true)
            ->withCount('products');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);

        return $this->handleCategoryContentNegotiation($request, $categories);
    }

    public function show(Request $request, Category $category): JsonResponse|View|Response
    {
        $category->load(['children', 'parent']);

        $data = [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                    'slug' => $category->parent->slug,
                ] : null,
                'children' => $category->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'description' => $child->description,
                    ];
                })->toArray(),
                'url' => route('category.show', $category->slug),
                'product_count' => $category->products_count ?? 0,
            ],
        ];

        return $this->handleContentNegotiation($request, $data);
    }
}
