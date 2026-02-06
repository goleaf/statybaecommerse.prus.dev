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
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * SearchWidget
 *
 * Livewire component for SearchWidget with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string     $query
 * @property array      $selectedCategories
 * @property array      $selectedBrands
 * @property array      $selectedAttributes
 * @property float|null $minPrice
 * @property float|null $maxPrice
 * @property string     $sortBy
 * @property string     $sortDirection
 * @property bool       $inStock
 * @property string     $viewMode
 * @property int        $perPage
 * @property mixed      $queryString
 */
final class SearchWidget extends Component
{
    use WithPagination;

    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $selectedCategories = [];

    public array $selectedBrands = [];

    public array $selectedAttributes = [];

    public ?float $minPrice = null;

    public ?float $maxPrice = null;

    public string $sortBy = 'relevance';

    public string $sortDirection = 'desc';

    public bool $inStock = false;

    public string $viewMode = 'grid';

    // grid, list
    public int $perPage = 12;

    protected $queryString = ['query' => ['except' => ''], 'selectedCategories' => ['except' => []], 'selectedBrands' => ['except' => []], 'selectedAttributes' => ['except' => []], 'minPrice' => ['except' => null], 'maxPrice' => ['except' => null], 'sortBy' => ['except' => 'relevance'], 'inStock' => ['except' => false], 'viewMode' => ['except' => 'grid']];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->query = request('q', '');
    }

    /**
     * Handle updatedQuery functionality with proper error handling.
     */
    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedSelectedCategories functionality with proper error handling.
     */
    public function updatedSelectedCategories(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedSelectedBrands functionality with proper error handling.
     */
    public function updatedSelectedBrands(): void
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
     * Handle clearFilters functionality with proper error handling.
     */
    public function clearFilters(): void
    {
        $this->reset(['selectedCategories', 'selectedBrands', 'selectedAttributes', 'minPrice', 'maxPrice', 'inStock']);
        $this->resetPage();
    }

    /**
     * Handle toggleCategory functionality with proper error handling.
     */
    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_diff($this->selectedCategories, [$categoryId]);
        } else {
            $this->selectedCategories[] = $categoryId;
        }
        $this->resetPage();
    }

    /**
     * Handle toggleBrand functionality with proper error handling.
     */
    public function toggleBrand(int $brandId): void
    {
        if (in_array($brandId, $this->selectedBrands)) {
            $this->selectedBrands = array_diff($this->selectedBrands, [$brandId]);
        } else {
            $this->selectedBrands[] = $brandId;
        }
        $this->resetPage();
    }

    /**
     * Handle toggleAttribute functionality with proper error handling.
     */
    public function toggleAttribute(int $attributeValueId): void
    {
        if (in_array($attributeValueId, $this->selectedAttributes)) {
            $this->selectedAttributes = array_diff($this->selectedAttributes, [$attributeValueId]);
        } else {
            $this->selectedAttributes[] = $attributeValueId;
        }
        $this->resetPage();
    }

    /**
     * Handle products functionality with proper error handling.
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        // Return empty paginator if no search query
        if (trim($this->query) === '') {
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
                'query'    => $this->query,
                'page'     => $this->getPage(),
                'per_page' => $this->perPage,
                'types'    => ['product'], // Only search for products
                'sort'     => $searchSort,
                'filters'  => $filters,
            ], [
                'source' => 'search-widget',
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
            logger()->error('SearchWidget search failed', [
                'query' => $this->query,
                'error' => $e->getMessage(),
            ]);

            return $this->createEmptyPaginator();
        }
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_visible', true)->whereHas('products')->withCount('products')->orderBy('name')->get();
    }

    /**
     * Handle brands functionality with proper error handling.
     */
    #[Computed]
    public function brands(): Collection
    {
        return Brand::where('is_visible', true)->whereHas('products')->withCount('products')->orderBy('name')->get();
    }

    /**
     * Handle attributes functionality with proper error handling.
     */
    #[Computed]
    public function attributes(): Collection
    {
        return Attribute::with(['values' => function ($query) {
            $query->whereHas('productVariants.product', function ($q) {
                $q->published();
            });
        }])->whereHas('values.productVariants.product', function ($q) {
            $q->published();
        })->orderBy('name')->get();
    }

    /**
     * Handle priceRange functionality with proper error handling.
     */
    #[Computed]
    public function priceRange(): array
    {
        $prices = ProductVariant::whereHas('product', function ($q) {
            $q->published();
        })->pluck('price');

        return ['min' => $prices->min() ?? 0, 'max' => $prices->max() ?? 1000];
    }

    /**
     * Handle activeFiltersCount functionality with proper error handling.
     */
    #[Computed]
    public function activeFiltersCount(): int
    {
        return count($this->selectedCategories) + count($this->selectedBrands) + count($this->selectedAttributes) + ($this->minPrice ? 1 : 0) + ($this->maxPrice ? 1 : 0) + ($this->inStock ? 1 : 0);
    }

    /**
     * Build search filters from component state
     */
    private function buildSearchFilters(): array
    {
        $filters = [];

        if (! empty($this->selectedCategories)) {
            $filters['categories'] = $this->selectedCategories;
        }

        if (! empty($this->selectedBrands)) {
            $filters['brands'] = $this->selectedBrands;
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

        return $filters;
    }

    /**
     * Map component sort parameter to SearchService format
     */
    private function mapSortParameter(string $sort): string
    {
        return match ($sort) {
            'price_asc'  => 'price_asc',
            'price_desc' => 'price_desc',
            'name'       => 'name_' . $this->sortDirection,
            'created_at' => 'created_at_' . $this->sortDirection,
            'rating'     => 'rating_desc',
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
                'id'                => $item['id'],
                'slug'              => $this->extractSlugFromUrl($item['url'] ?? ''),
                'name'              => $item['title'] ?? '',
                'short_description' => $item['description'] ?? '',
                'brand_id'          => null, // Not available in search results
                'published_at'      => now(), // Assume published since it's in results
                'brand'             => $item['subtitle'] ? (object) ['name' => $item['subtitle']] : null,
                'media'             => collect(), // Empty collection for compatibility
                'prices'            => collect(), // Empty collection for compatibility
                'variants_count'    => 0, // Not available in search results
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
        return view('livewire.components.search', ['products' => $this->products, 'categories' => $this->categories, 'brands' => $this->brands, 'attributes' => $this->attributes, 'priceRange' => $this->priceRange, 'activeFiltersCount' => $this->activeFiltersCount]);
    }
}
