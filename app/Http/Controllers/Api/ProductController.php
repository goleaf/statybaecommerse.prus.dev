<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Contracts\Entities\ProductContract;
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
        $payload = ProductContract::forCollection($filteredProducts, [
            'query' => $query,
            'total' => $filteredProducts->count(),
            'limit' => $limit,
        ]);

        return $this->respondWithContract($request, $payload);
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

        $payload = ProductContract::forCollection($paginatedData, [
            'total' => $total,
            'limit' => $perPage,
        ]);

        return $this->respondWithContract($request, $payload);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Product $product): JsonResponse|View|Response
    {
        $product->load(['brand', 'media', 'category', 'variants']);
        $payload = ProductContract::forProduct($product);

        return $this->respondWithContract($request, $payload);
    }
}
