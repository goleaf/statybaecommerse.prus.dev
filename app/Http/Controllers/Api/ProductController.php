<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Database\Eloquent\Builder;
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
        $definition = $this->productCatalogDefinition();
        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = Product::query()
            ->where('is_visible', true)
            ->where('price', '>', 0)
            ->whereNotNull('slug')
            ->where('slug', '<>', '')
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->with(['brand', 'media', 'category']);

        $paginator = $listQuery->apply($query, $definition);

        $response = ListResponse::fromPaginator(
            $paginator,
            $listQuery,
            static fn (Product $product): array => [
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
                'is_visible' => (bool) $product->is_visible,
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

        return $this->handleProductContentNegotiation(
            $request,
            $paginator,
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

    private function productCatalogDefinition(): ListQueryDefinition
    {
        return ListQueryDefinition::make()
            ->defaultPerPage(20)
            ->maxPerPage(100)
            ->defaultSort('name', 'asc')
            ->allowedSorts([
                'name' => ['column' => ['name', 'id']],
                'price' => ['column' => 'price'],
                'sale_price' => ['column' => 'sale_price'],
                'created_at' => ['column' => 'created_at'],
            ])
            ->filters([
                'category' => [
                    'type' => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $slug): void {
                        $builder->whereHas('category', static function (Builder $query) use ($slug): void {
                            $query->where('slug', $slug);
                        });
                    },
                ],
                'brand' => [
                    'type' => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $slug): void {
                        $builder->whereHas('brand', static function (Builder $query) use ($slug): void {
                            $query->where('slug', $slug);
                        });
                    },
                ],
            ]);
    }
}
