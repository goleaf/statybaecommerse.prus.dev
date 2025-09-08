<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

final class CategoryController extends Controller
{
    public function tree(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_visible', true)
            ->with(['children' => function ($query) {
                $query->where('is_visible', true)
                      ->orderBy('sort_order')
                      ->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'url' => route('categories.show', $category->slug),
                    'children' => $category->children->map(function (Category $child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'description' => $child->description,
                            'url' => route('categories.show', $child->slug),
                        ];
                    }),
                ];
            });

        return response()->json([
            'data' => $categories,
            'total' => $categories->count(),
        ]);
    }
}

