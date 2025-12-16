<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Data\SearchQueryData;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SearchService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * ProductSearchWidget
 *
 * Livewire component for ProductSearchWidget with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string     $search
 * @property array      $categories
 * @property array      $brands
 * @property array      $selectedAttributes
 * @property float|null $minPrice
 * @property float|null $maxPrice
 * @property string     $sortBy
 * @property string     $sortDirection
 * @property bool       $inStock
 * @property bool       $onSale
 * @property bool       $featured
 * @property string     $viewMode
 * @property int        $perPage
 * @property bool       $showFilters
 */
final class ProductSearchWidget extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: [])]
    public array $categories = [];

    #[Url(except: [])]
    public array $brands = [];

    #[Url(except: [])]
    public array $selectedAttributes = [];

    #[Url(except: null)]
    public ?float $minPrice = null;

    #[Url(except: null)]
    public ?float $maxPrice = null;

    #[Url(except: 'relevance')]
    public string $sortBy = 'relevance';

    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';

    #[Url(except: false)]
    public bool $inStock = false;

    #[Url(except: false)]
    public bool $onSale = false;

    #[Url(except: false)]
    public bool $featured = false;

    #[Url(except: 'grid')]
    public string $viewMode = 'grid';

    public int $perPage = 12;

    public bool $showFilters = false;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Set initial price range if not set
        if ($this->minPrice === null || $this->maxPrice === null) {
            $priceRange = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
            $this->minPrice = $this->minPrice ?? $priceRange->min_price ?? 0;
            $this->maxPrice = $this->maxPrice ?? $priceRange->max_price ?? 1000;
        }
    }

    /**
     * Handle updatedSearch functionality with proper error handling.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedCategories functionality with proper error handling.
     */
    public function updatedCategories(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedBrands functionality with proper error handling.
     */
    public function updatedBrands(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedSelectedAttributes functionality with proper error handling.
     */
    public function updatedSelectedAttributes(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedMinPrice functionality with proper error handling.
     */
    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedMaxPrice functionality with proper error handling.
     */
    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedSortBy functionality with proper error handling.
     */
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedInStock functionality with proper error handling.
     */
    public function updatedInStock(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedOnSale functionality with proper error handling.
     */
    public function updatedOnSale(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedFeatured functionality with proper error handling.
     */
    public function updatedFeatured(): void
    {
        $this->resetPage();
    }

    /**
     * Handle clearFilters functionality with proper error handling.
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'categories', 'brands', 'selectedAttributes', 'minPrice', 'maxPrice', 'inStock', 'onSale', 'featured']);
        $this->sortBy = 'relevance';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    /**
     * Handle toggleFilters functionality with proper error handling.
     */
    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    /**
     * Handle products functionality with proper error handling.
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        // Return empty paginator if no search query
        if (trim($this->search) === '') {
            return $this->createEmptyPaginator();
        }

        try {
            $searchService = app(SearchService::class);

            // Build filters from component state
            $filters = $this->buildSearchFilters();

            // Map sort parameter to SearchService format
            $searchSort = $this->mapSortParameter($this->sortBy);

            // Create search query data for products only
            $queryData = SearchQueryData::fromArray([
                'query'    => $this->search,
                'page'     => $this->getPage(),
                'per_page' => $this->perPage,
                'types'    => ['product'], // Only search for products
                'sort'     => $searchSort,
                'filters'  => $filters,
            ], [
                'source' => 'product-search-widget',
                'locale' => app()->getLocale(),
            ]);

            // Execute search through SearchService
            $searchResults = $searchService->search($queryData);

            // Extract product data from search results
            $products = $this->extractProductsFromSearchResults($searchResults);

            // Convert to paginator format expected by the view
            return $this->createPaginatorFromSearchResults($products, $searchResults['meta'] ?? []);

        } catch (Exception $e) {
            // Log error and return empty results on failure
            logger()->error('ProductSearchWidget search failed', [
                'query' => $this->search,
                'error' => $e->getMessage(),
            ]);

            return $this->createEmptyPaginator();
        }
    }

    /**
     * Handle getCategoriesProperty functionality with proper error handling.
     */
    public function getCategoriesProperty(): Collection
    {
        return Category::where('is_visible', true)->whereHas('products')->withCount('products')->orderBy('name')->get();
    }

    /**
     * Handle getBrandsProperty functionality with proper error handling.
     */
    public function getBrandsProperty(): Collection
    {
        return Brand::where('is_enabled', true)->whereHas('products')->withCount('products')->orderBy('name')->get();
    }

    /**
     * Handle getAttributesProperty functionality with proper error handling.
     */
    public function getAttributesProperty(): Collection
    {
        return Attribute::where('is_filterable', true)->with(['values' => function ($query) {
            $query->whereHas('products')->orderBy('name');
        }])->orderBy('name')->get();
    }

    /**
     * Build search filters from component state
     */
    private function buildSearchFilters(): array
    {
        $filters = [];

        if (! empty($this->categories)) {
            $filters['categories'] = $this->categories;
        }

        if (! empty($this->brands)) {
            $filters['brands'] = $this->brands;
        }

        if (! empty($this->selectedAttributes)) {
            $filters['attributes'] = $this->selectedAttributes;
        }

        if ($this->minPrice !== null) {
            $filters['min_price'] = $this->minPrice;
        }

        if ($this->maxPrice !== null) {
            $filters['max_price'] = $this->maxPrice;
        }

        if ($this->inStock) {
            $filters['in_stock'] = true;
        }

        if ($this->onSale) {
            $filters['on_sale'] = true;
        }

        if ($this->featured) {
            $filters['featured'] = true;
        }

        return $filters;
    }

    /**
     * Map component sort parameter to SearchService format
     */
    private function mapSortParameter(string $sort): string
    {
        return match ($sort) {
            'name'       => 'name_' . $this->sortDirection,
            'price'      => 'price_' . $this->sortDirection,
            'created_at' => 'created_at_' . $this->sortDirection,
            'popularity' => 'popularity_' . $this->sortDirection,
            'rating'     => 'rating_' . $this->sortDirection,
            default      => 'relevance',
        };
    }

    /**
     * Extract product data from SearchService results
     */
    private function extractProductsFromSearchResults(array $searchResults): \Illuminate\Support\Collection
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
    private function createPaginatorFromSearchResults(\Illuminate\Support\Collection $products, array $meta): LengthAwarePaginator
    {
        $currentPage = $meta['page'] ?? 1;
        $perPage = $meta['per_page'] ?? $this->perPage;
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
            $this->perPage,
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
        return view('livewire.components.advanced-product-search');
    }
}
