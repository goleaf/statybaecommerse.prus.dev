<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final /**
 * ProductController
 * 
 * HTTP controller handling web requests and responses with content negotiation.
 */
class ProductController extends Controller
{
    use HandlesContentNegotiation;

    public function search(Request $request): JsonResponse|View|Response
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
            ->with(['brand', 'media', 'category'])
            ->limit($limit)
            ->get();

        $data = [
            'products' => $products->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'image' => $product->getFirstMediaUrl('images', 'thumb'),
                    'url' => route('product.show', $product->slug),
                    'stock_quantity' => $product->stock_quantity ?? 0,
                ];
            })->toArray(),
            'query' => $query,
            'total' => $products->count(),
            'limit' => $limit,
        ];

        return $this->handleContentNegotiation($request, $data);
    }

    public function catalog(Request $request): JsonResponse|View|Response
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $category = $request->get('category');
        $brand = $request->get('brand');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        $query = Product::query()
            ->where('is_visible', true)
            ->with(['brand', 'media', 'category']);

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        if ($brand) {
            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where('slug', $brand);
            });
        }

        $products = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        return $this->handleProductContentNegotiation($request, $products);
    }

    public function show(Request $request, Product $product): JsonResponse|View|Response
    {
        $product->load(['brand', 'media', 'category', 'variants']);

        $data = [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'description' => $product->description,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'images' => $product->getMedia('images')->map(function ($media) {
                    return [
                        'url' => $media->getUrl(),
                        'thumb' => $media->getUrl('thumb'),
                        'alt' => $media->getCustomProperty('alt', ''),
                    ];
                })->toArray(),
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'stock_quantity' => $variant->stock_quantity,
                    ];
                })->toArray(),
                'stock_quantity' => $product->stock_quantity ?? 0,
                'is_visible' => $product->is_visible,
                'url' => route('product.show', $product->slug),
            ],
        ];

        return $this->handleContentNegotiation($request, $data);
    }
}
