<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Data\SearchQueryData;
use App\Models\Product;
use App\Services\SearchService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Search
 *
 * Livewire component for Search with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string      $q
 * @property string|null $sort
 */
#[Layout('components.layouts.base')]
class Search extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public ?string $sort = null;

    private SearchService $searchService;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function mount(): void
    {
        $this->searchService = app(SearchService::class);
    }

    /**
     * Handle searchResults functionality with proper error handling.
     */
    #[Computed]
    public function searchResults(): LengthAwarePaginator
    {
        // Return empty paginator if no search query
        if (trim($this->q) === '') {
            return $this->createEmptyPaginator();
        }

        try {
            // Map sort parameter to SearchService format
            $searchSort = $this->mapSortParameter($this->sort);

            // Create search query data for products only
            $queryData = SearchQueryData::fromArray([
                'query'    => $this->q,
                'page'     => $this->getPage(),
                'per_page' => 12,
                'types'    => ['product'], // Only search for products
                'sort'     => $searchSort,
            ], [
                'source' => 'storefront-search',
                'locale' => app()->getLocale(),
            ]);

            // Execute search through SearchService
            $searchResults = $this->searchService->search($queryData);

            // Extract product data from search results
            $products = $this->extractProductsFromSearchResults($searchResults);

            // Convert to paginator format expected by the view
            return $this->createPaginatorFromSearchResults($products, $searchResults['meta'] ?? []);

        } catch (Exception $e) {
            // Log error and return empty results on failure
            logger()->error('Search failed', [
                'query' => $this->q,
                'error' => $e->getMessage(),
            ]);

            return $this->createEmptyPaginator();
        }
    }

    /**
     * Map component sort parameter to SearchService format
     */
    private function mapSortParameter(?string $sort): string
    {
        return match ($sort) {
            'name_asc'  => 'relevance', // SearchService doesn't have name sorting, use relevance
            'name_desc' => 'relevance',
            default     => 'relevance',
        };
    }

    /**
     * Extract product data from SearchService results
     */
    private function extractProductsFromSearchResults(array $searchResults): Collection
    {
        $products = collect();

        // Get products from the aggregated data structure
        if (isset($searchResults['data']['products']['items'])) {
            $productItems = $searchResults['data']['products']['items'];
        } elseif (isset($searchResults['data']) && is_array($searchResults['data'])) {
            // Handle flat data structure
            $productItems = array_filter($searchResults['data'], fn ($item) => is_array($item) && ($item['type'] ?? null) === 'product'
            );
        } else {
            $productItems = [];
        }

        foreach ($productItems as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            // Create a Product-like object from search result data
            $product = (object) [
                'id'             => $item['id'],
                'slug'           => $this->extractSlugFromUrl($item['url'] ?? ''),
                'name'           => $item['title'] ?? '',
                'summary'        => $item['description'] ?? '',
                'brand_id'       => null, // Not available in search results
                'published_at'   => now(), // Assume published since it's in results
                'brand'          => $item['subtitle'] ? (object) ['name' => $item['subtitle']] : null,
                'media'          => collect(), // Empty collection for compatibility
                'prices'         => collect(), // Empty collection for compatibility
                'variants_count' => 0, // Not available in search results
            ];

            $products->push($product);
        }

        return $products;
    }

    /**
     * Extract slug from product URL
     */
    private function extractSlugFromUrl(string $url): string
    {
        if (empty($url)) {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! $path) {
            return '';
        }

        $segments = explode('/', trim($path, '/'));

        return end($segments) ?: '';
    }

    /**
     * Create paginator from search results
     */
    private function createPaginatorFromSearchResults(Collection $products, array $meta): LengthAwarePaginator
    {
        $currentPage = $meta['page'] ?? 1;
        $perPage = $meta['per_page'] ?? 12;
        $total = $meta['total_results'] ?? 0;

        return new Paginator(
            $products,
            $total,
            $perPage,
            $currentPage,
            [
                'path'     => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Create empty paginator for no results
     */
    private function createEmptyPaginator(): LengthAwarePaginator
    {
        return new Paginator(
            collect(),
            0,
            12,
            1,
            [
                'path'     => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.search', ['products' => $this->searchResults, 'term' => $this->q])->title(__('messages.frontend'));
    }
}
