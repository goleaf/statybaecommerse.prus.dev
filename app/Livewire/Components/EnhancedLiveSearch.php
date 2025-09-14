<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
/**
 * EnhancedLiveSearch
 * 
 * Livewire component for EnhancedLiveSearch with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $query
 * @property array $results
 * @property array $suggestions
 * @property bool $showResults
 * @property bool $showSuggestions
 * @property int $maxResults
 * @property int $minQueryLength
 * @property bool $isSearching
 * @property array $searchTypes
 * @property bool $enableSuggestions
 * @property bool $enableRecentSearches
 * @property bool $enablePopularSearches
 * @property bool $enableAnalytics
 * @property string $selectedCategory
 * @property string $selectedBrand
 * @property float $minPrice
 * @property float $maxPrice
 * @property bool $inStockOnly
 * @property string $sortBy
 */
final class EnhancedLiveSearch extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';
    public array $results = [];
    public array $suggestions = [];
    public bool $showResults = false;
    public bool $showSuggestions = false;
    public int $maxResults = 15;
    public int $minQueryLength = 2;
    public bool $isSearching = false;
    public array $searchTypes = ['products', 'categories', 'brands', 'collections'];
    public bool $enableSuggestions = true;
    public bool $enableRecentSearches = true;
    public bool $enablePopularSearches = true;
    public bool $enableAnalytics = true;
    public string $selectedCategory = '';
    public string $selectedBrand = '';
    public float $minPrice = 0;
    public float $maxPrice = 10000;
    public bool $inStockOnly = false;
    public string $sortBy = 'relevance';
    // relevance, price_asc, price_desc, newest, rating
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        if ($this->enableSuggestions) {
            $this->loadSuggestions();
        }
    }
    /**
     * Handle updatedQuery functionality with proper error handling.
     * @return void
     */
    public function updatedQuery(): void
    {
        if (strlen($this->query) >= $this->minQueryLength) {
            $this->isSearching = true;
            $this->performSearch();
            $this->showResults = true;
            $this->showSuggestions = false;
            if ($this->enableAnalytics) {
                $this->trackSearch();
            }
        } else {
            $this->clearResults();
            if ($this->enableSuggestions && empty($this->query)) {
                $this->loadSuggestions();
                $this->showSuggestions = true;
            } else {
                $this->showSuggestions = false;
            }
        }
    }
    /**
     * Handle updatedSelectedCategory functionality with proper error handling.
     * @return void
     */
    public function updatedSelectedCategory(): void
    {
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    /**
     * Handle updatedSelectedBrand functionality with proper error handling.
     * @return void
     */
    public function updatedSelectedBrand(): void
    {
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    /**
     * Handle updatedMinPrice functionality with proper error handling.
     * @return void
     */
    public function updatedMinPrice(): void
    {
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    /**
     * Handle updatedMaxPrice functionality with proper error handling.
     * @return void
     */
    public function updatedMaxPrice(): void
    {
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    /**
     * Handle updatedInStockOnly functionality with proper error handling.
     * @return void
     */
    public function updatedInStockOnly(): void
    {
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    /**
     * Handle updatedSortBy functionality with proper error handling.
     * @return void
     */
    public function updatedSortBy(): void
    {
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    /**
     * Handle searchResults functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 300)]
    public function searchResults(): array
    {
        if (strlen($this->query) < $this->minQueryLength) {
            return [];
        }
        $cacheKey = $this->generateCacheKey();
        return Cache::remember($cacheKey, 300, function () {
            $autocompleteService = app(AutocompleteService::class);
            $searchParams = ['query' => $this->query, 'max_results' => $this->maxResults, 'search_types' => $this->searchTypes, 'category' => $this->selectedCategory, 'brand' => $this->selectedBrand, 'min_price' => $this->minPrice, 'max_price' => $this->maxPrice, 'in_stock_only' => $this->inStockOnly, 'sort_by' => $this->sortBy];
            return $autocompleteService->advancedSearch($searchParams);
        });
    }
    /**
     * Handle performSearch functionality with proper error handling.
     * @return void
     */
    public function performSearch(): void
    {
        $this->results = $this->searchResults;
        $this->isSearching = false;
    }
    /**
     * Handle cachedSuggestions functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 600)]
    public function cachedSuggestions(): array
    {
        if (!$this->enableSuggestions) {
            return [];
        }
        return Cache::remember('enhanced_search_suggestions', 600, function () {
            $autocompleteService = app(AutocompleteService::class);
            $suggestions = [];
            if ($this->enableRecentSearches) {
                $recent = $autocompleteService->getRecentSuggestions(5);
                $suggestions = array_merge($suggestions, $recent);
            }
            if ($this->enablePopularSearches) {
                $popular = $autocompleteService->getPopularSuggestions(10);
                $suggestions = array_merge($suggestions, $popular);
            }
            return array_slice($suggestions, 0, 15);
        });
    }
    /**
     * Handle loadSuggestions functionality with proper error handling.
     * @return void
     */
    public function loadSuggestions(): void
    {
        $this->suggestions = $this->cachedSuggestions;
    }
    /**
     * Handle clearResults functionality with proper error handling.
     * @return void
     */
    public function clearResults(): void
    {
        $this->results = [];
        $this->showResults = false;
        $this->isSearching = false;
    }
    /**
     * Handle clearQuery functionality with proper error handling.
     * @return void
     */
    public function clearQuery(): void
    {
        $this->query = '';
        $this->clearResults();
        $this->resetFilters();
        if ($this->enableSuggestions) {
            $this->loadSuggestions();
            $this->showSuggestions = true;
        }
    }
    /**
     * Handle resetFilters functionality with proper error handling.
     * @return void
     */
    public function resetFilters(): void
    {
        $this->selectedCategory = '';
        $this->selectedBrand = '';
        $this->minPrice = 0;
        $this->maxPrice = 10000;
        $this->inStockOnly = false;
        $this->sortBy = 'relevance';
    }
    /**
     * Handle selectResult functionality with proper error handling.
     * @param array $result
     * @return void
     */
    public function selectResult(array $result): void
    {
        $this->query = $result['title'];
        $this->clearResults();
        $this->redirect($result['url']);
    }
    /**
     * Handle selectSuggestion functionality with proper error handling.
     * @param array $suggestion
     * @return void
     */
    public function selectSuggestion(array $suggestion): void
    {
        if (isset($suggestion['search_term'])) {
            $this->query = $suggestion['search_term'];
        } else {
            $this->query = $suggestion['title'];
        }
        $this->showSuggestions = false;
        $this->updatedQuery();
    }
    /**
     * Handle clearRecentSearches functionality with proper error handling.
     * @return void
     */
    public function clearRecentSearches(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        $autocompleteService->clearRecentSearches();
        $this->loadSuggestions();
    }
    /**
     * Handle toggleSearchType functionality with proper error handling.
     * @param string $type
     * @return void
     */
    public function toggleSearchType(string $type): void
    {
        if (in_array($type, $this->searchTypes)) {
            $this->searchTypes = array_filter($this->searchTypes, fn($t) => $t !== $type);
        } else {
            $this->searchTypes[] = $type;
        }
        if (!empty($this->query) && strlen($this->query) >= $this->minQueryLength) {
            $this->performSearch();
        }
    }
    /**
     * Handle quickFilter functionality with proper error handling.
     * @param string $filter
     * @param mixed $value
     * @return void
     */
    public function quickFilter(string $filter, $value): void
    {
        match ($filter) {
            'category' => $this->selectedCategory = $value,
            'brand' => $this->selectedBrand = $value,
            'price_range' => $this->setPriceRange($value),
            'stock' => $this->inStockOnly = $value === 'in_stock',
            'sort' => $this->sortBy = $value,
            default => null,
        };
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    /**
     * Handle setPriceRange functionality with proper error handling.
     * @param string $range
     * @return void
     */
    private function setPriceRange(string $range): void
    {
        match ($range) {
            'under_50' => [$this->minPrice, $this->maxPrice] = [0, 50],
            '50_100' => [$this->minPrice, $this->maxPrice] = [50, 100],
            '100_500' => [$this->minPrice, $this->maxPrice] = [100, 500],
            'over_500' => [$this->minPrice, $this->maxPrice] = [500, 10000],
            default => [$this->minPrice, $this->maxPrice] = [0, 10000],
        };
    }
    /**
     * Handle generateCacheKey functionality with proper error handling.
     * @return string
     */
    private function generateCacheKey(): string
    {
        return 'enhanced_search_' . md5(json_encode(['query' => $this->query, 'max_results' => $this->maxResults, 'search_types' => $this->searchTypes, 'category' => $this->selectedCategory, 'brand' => $this->selectedBrand, 'min_price' => $this->minPrice, 'max_price' => $this->maxPrice, 'in_stock_only' => $this->inStockOnly, 'sort_by' => $this->sortBy]));
    }
    /**
     * Handle trackSearch functionality with proper error handling.
     * @return void
     */
    private function trackSearch(): void
    {
        // Track search analytics
        Cache::increment("search_count_{$this->query}", 1);
        Cache::put("last_search_{$this->query}", now(), 3600);
    }
    /**
     * Handle clearSearchCache functionality with proper error handling.
     * @return void
     */
    #[On('clear-search-cache')]
    public function clearSearchCache(): void
    {
        Cache::forget('enhanced_search_suggestions');
        $this->loadSuggestions();
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.enhanced-live-search');
    }
}