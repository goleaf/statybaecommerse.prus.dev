<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
/**
 * AdvancedSearch
 * 
 * Livewire component for AdvancedSearch with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $query
 * @property array $results
 * @property bool $showResults
 * @property bool $showFilters
 * @property int $maxResults
 * @property int $minQueryLength
 * @property bool $isSearching
 * @property array $selectedCategories
 * @property array $selectedBrands
 * @property array $selectedCollections
 * @property float|null $minPrice
 * @property float|null $maxPrice
 * @property bool $inStockOnly
 * @property string $sortBy
 * @property array $availableCategories
 * @property array $availableBrands
 * @property array $availableCollections
 */
final class AdvancedSearch extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';
    public array $results = [];
    public bool $showResults = false;
    public bool $showFilters = false;
    public int $maxResults = 20;
    public int $minQueryLength = 2;
    public bool $isSearching = false;
    // Filter properties
    public array $selectedCategories = [];
    public array $selectedBrands = [];
    public array $selectedCollections = [];
    public ?float $minPrice = null;
    public ?float $maxPrice = null;
    public bool $inStockOnly = false;
    public string $sortBy = 'relevance';
    // Available options for filters
    public array $availableCategories = [];
    public array $availableBrands = [];
    public array $availableCollections = [];
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->loadFilterOptions();
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
        } else {
            $this->clearResults();
        }
    }
    /**
     * Handle performSearch functionality with proper error handling.
     * @return void
     */
    public function performSearch(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        // Get base results
        $baseResults = $autocompleteService->search($this->query, $this->maxResults * 2);
        // Apply filters
        $this->results = $this->applyFilters($baseResults);
        // Sort results
        $this->results = $this->sortResults($this->results);
        // Limit results
        $this->results = array_slice($this->results, 0, $this->maxResults);
        $this->isSearching = false;
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
     * Handle toggleFilters functionality with proper error handling.
     * @return void
     */
    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }
    /**
     * Handle clearFilters functionality with proper error handling.
     * @return void
     */
    public function clearFilters(): void
    {
        $this->selectedCategories = [];
        $this->selectedBrands = [];
        $this->selectedCollections = [];
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->inStockOnly = false;
        $this->sortBy = 'relevance';
        if ($this->query) {
            $this->performSearch();
        }
    }
    /**
     * Handle applyFilters functionality with proper error handling.
     * @param array $results
     * @return array
     */
    public function applyFilters(array $results): array
    {
        return array_filter($results, function ($result) {
            // Category filter
            if (!empty($this->selectedCategories) && $result['type'] === 'product') {
                if (!isset($result['categories']) || !array_intersect($result['categories'], $this->selectedCategories)) {
                    return false;
                }
            }
            // Brand filter
            if (!empty($this->selectedBrands) && $result['type'] === 'product') {
                if (!isset($result['brand_id']) || !in_array($result['brand_id'], $this->selectedBrands)) {
                    return false;
                }
            }
            // Collection filter
            if (!empty($this->selectedCollections) && $result['type'] === 'product') {
                if (!isset($result['collections']) || !array_intersect($result['collections'], $this->selectedCollections)) {
                    return false;
                }
            }
            // Price filter
            if ($result['type'] === 'product' && isset($result['price'])) {
                if ($this->minPrice !== null && $result['price'] < $this->minPrice) {
                    return false;
                }
                if ($this->maxPrice !== null && $result['price'] > $this->maxPrice) {
                    return false;
                }
            }
            // Stock filter
            if ($this->inStockOnly && $result['type'] === 'product') {
                if (!isset($result['in_stock']) || !$result['in_stock']) {
                    return false;
                }
            }
            return true;
        });
    }
    /**
     * Handle sortResults functionality with proper error handling.
     * @param array $results
     * @return array
     */
    public function sortResults(array $results): array
    {
        return match ($this->sortBy) {
            'price_low' => $this->sortByPrice($results, 'asc'),
            'price_high' => $this->sortByPrice($results, 'desc'),
            'name' => $this->sortByName($results),
            'relevance' => $this->sortByRelevance($results),
            default => $results,
        };
    }
    /**
     * Handle sortByPrice functionality with proper error handling.
     * @param array $results
     * @param string $direction
     * @return array
     */
    private function sortByPrice(array $results, string $direction): array
    {
        usort($results, function ($a, $b) use ($direction) {
            $priceA = $a['price'] ?? 0;
            $priceB = $b['price'] ?? 0;
            return $direction === 'asc' ? $priceA <=> $priceB : $priceB <=> $priceA;
        });
        return $results;
    }
    /**
     * Handle sortByName functionality with proper error handling.
     * @param array $results
     * @return array
     */
    private function sortByName(array $results): array
    {
        usort($results, function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        return $results;
    }
    /**
     * Handle sortByRelevance functionality with proper error handling.
     * @param array $results
     * @return array
     */
    private function sortByRelevance(array $results): array
    {
        usort($results, function ($a, $b) {
            $scoreA = $a['relevance_score'] ?? 0;
            $scoreB = $b['relevance_score'] ?? 0;
            return $scoreB <=> $scoreA;
        });
        return $results;
    }
    /**
     * Handle loadFilterOptions functionality with proper error handling.
     * @return void
     */
    private function loadFilterOptions(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        // Load categories
        $categories = $autocompleteService->searchCategories('', 50);
        $this->availableCategories = array_map(function ($cat) {
            return ['id' => $cat['id'], 'name' => $cat['title']];
        }, $categories);
        // Load brands
        $brands = $autocompleteService->searchBrands('', 50);
        $this->availableBrands = array_map(function ($brand) {
            return ['id' => $brand['id'], 'name' => $brand['title']];
        }, $brands);
        // Load collections
        $collections = $autocompleteService->searchCollections('', 50);
        $this->availableCollections = array_map(function ($collection) {
            return ['id' => $collection['id'], 'name' => $collection['title']];
        }, $collections);
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.advanced-search');
    }
}