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

/**
 * ProductController
 *
 * HTTP controller handling ProductController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductController extends Controller
{
    use HandlesContentNegotiation;

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): JsonResponse|View|Response
    {
        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 10), 50);
        // Use LazyCollection with timeout to prevent long-running search operations
        $timeout = now()->addSeconds(10);
        // 10 second timeout for product search
        $products = Product::query()->where('is_visible', true)->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")->orWhere('description', 'like', "%{$query}%")->orWhere('sku', 'like', "%{$query}%");
        })->with(['brand', 'media', 'category'])->cursor()->takeUntilTimeout($timeout)->take($limit)->collect();
        // Apply skipWhile to filter out products that are not properly configured
        $filteredProducts = $products->skipWhile(function (Product $product) {
            return empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug);
        });
        $data = ['products' => $filteredProducts->map(function (Product $product) {
            return ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'sku' => $product->sku, 'price' => $product->price, 'sale_price' => $product->sale_price, 'brand' => $product->brand?->name, 'category' => $product->category?->name, 'image' => $product->getFirstMediaUrl('images', 'thumb'), 'url' => route('product.show', $product->slug), 'stock_quantity' => $product->stock_quantity ?? 0];
        })->toArray(), 'query' => $query, 'total' => $filteredProducts->count(), 'limit' => $limit];

        return $this->handleContentNegotiation($request, $data);
    }

    /**
     * Handle catalog functionality with proper error handling.
     */
    public function catalog(Request $request): JsonResponse|View|Response
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $category = $request->get('category');
        $brand = $request->get('brand');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query = Product::query()->where('is_visible', true)->with(['brand', 'media', 'category']);
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
        $products = $query->orderBy($sortBy, $sortOrder)->get()->skipWhile(function (Product $product) {
            // Skip products that are not properly configured for catalog display
            return empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug);
        });
        // Apply pagination manually after skipWhile filtering
        $total = $products->count();
        $currentPage = (int) $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedProducts = $products->slice($offset, $perPage);
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator($paginatedProducts, $total, $perPage, $currentPage, ['path' => $request->url(), 'pageName' => 'page']);

        return $this->handleProductContentNegotiation($request, $paginatedData);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Product $product): JsonResponse|View|Response
    {
        $product->load(['brand', 'media', 'category', 'variants']);
        $data = ['product' => ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'sku' => $product->sku, 'description' => $product->description, 'price' => $product->price, 'sale_price' => $product->sale_price, 'brand' => $product->brand?->name, 'category' => $product->category?->name, 'images' => $product->getMedia('images')->map(function ($media) {
            return ['url' => $media->getUrl(), 'thumb' => $media->getUrl('thumb'), 'alt' => $media->getCustomProperty('alt', '')];
        })->toArray(), 'variants' => $product->variants->map(function ($variant) {
            return ['id' => $variant->id, 'name' => $variant->name, 'sku' => $variant->sku, 'price' => $variant->price, 'stock_quantity' => $variant->stock_quantity];
        })->toArray(), 'stock_quantity' => $product->stock_quantity ?? 0, 'is_visible' => $product->is_visible, 'url' => route('product.show', $product->slug)]];

        return $this->handleContentNegotiation($request, $data);
    }
}
