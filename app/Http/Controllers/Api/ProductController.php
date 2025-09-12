<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 10), 50);

        $products = Product::query()
            ->where('is_visible', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->with(['brand', 'media'])
            ->limit($limit)
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'brand' => $product->brand?->name,
                    'image' => $product->getFirstMediaUrl('images', 'thumb'),
                    'url' => route('product.show', $product->slug),
                ];
            });

        return response()->json([
            'data' => $products,
            'query' => $query,
            'total' => $products->count(),
        ]);
    }
}
