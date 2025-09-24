<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\SearchPaginationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * InfiniteScrollSearch
 *
 * Livewire component for infinite scroll search functionality with proper error handling and logging.
 */
final class InfiniteScrollSearch extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $results = [];

    public array $filters = [];

    public array $availableFilters = [];

    public int $page = 1;

    public int $perPage = 20;

    public bool $hasMore = false;

    public bool $isLoading = false;

    public bool $showFilters = false;

    public array $selectedTypes = ['products', 'categories', 'brands'];

    public string $sortBy = 'relevance';

    public string $sortOrder = 'desc';

    public function mount(): void
    {
        $this->loadInitialResults();
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= 2) {
            $this->page = 1;
            $this->results = [];
            $this->loadResults();
        } else {
            $this->clearResults();
        }
    }

    public function updatedFilters(): void
    {
        $this->page = 1;
        $this->results = [];
        $this->loadResults();
    }

    public function updatedSelectedTypes(): void
    {
        $this->page = 1;
        $this->results = [];
        $this->loadResults();
    }

    public function updatedSortBy(): void
    {
        $this->page = 1;
        $this->results = [];
        $this->loadResults();
    }

    public function updatedSortOrder(): void
    {
        $this->page = 1;
        $this->results = [];
        $this->loadResults();
    }

    public function loadMore(): void
    {
        if ($this->hasMore && ! $this->isLoading) {
            $this->page++;
            $this->loadResults(true);
        }
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;

        if ($this->showFilters && empty($this->availableFilters)) {
            $this->loadAvailableFilters();
        }
    }

    public function clearFilters(): void
    {
        $this->filters = [];
        $this->page = 1;
        $this->results = [];
        $this->loadResults();
    }

    public function applyFilter(string $filterType, $value): void
    {
        if (empty($value)) {
            unset($this->filters[$filterType]);
        } else {
            $this->filters[$filterType] = $value;
        }

        $this->page = 1;
        $this->results = [];
        $this->loadResults();
    }

    public function removeFilter(string $filterType): void
    {
        unset($this->filters[$filterType]);
        $this->page = 1;
        $this->results = [];
        $this->loadResults();
    }

    public function clearResults(): void
    {
        $this->results = [];
        $this->page = 1;
        $this->hasMore = false;
        $this->isLoading = false;
    }

    public function render(): View
    {
        return view('livewire.components.infinite-scroll-search');
    }

    private function loadInitialResults(): void
    {
        if (strlen($this->query) >= 2) {
            $this->loadResults();
        }
    }

    private function loadResults(bool $append = false): void
    {
        if (strlen($this->query) < 2) {
            return;
        }

        $this->isLoading = true;

        try {
            $paginationService = app(SearchPaginationService::class);

            $searchData = $paginationService->getInfiniteScrollData(
                $this->query,
                $this->page,
                $this->perPage,
                $this->filters,
                $this->selectedTypes
            );

            if ($append) {
                $this->results = array_merge($this->results, $searchData['data']);
            } else {
                $this->results = $searchData['data'];
            }

            $this->hasMore = $searchData['infinite_scroll']['has_more'] ?? false;

            // Load available filters on first load
            if ($this->page === 1 && empty($this->availableFilters)) {
                $this->availableFilters = $paginationService->getAvailableFilters($this->results);
            }

        } catch (\Exception $e) {
            \Log::warning('Infinite scroll search failed: '.$e->getMessage());
            $this->addError('search', 'Search failed. Please try again.');
        } finally {
            $this->isLoading = false;
        }
    }

    private function loadAvailableFilters(): void
    {
        try {
            $paginationService = app(SearchPaginationService::class);
            $this->availableFilters = $paginationService->getAvailableFilters($this->results);
        } catch (\Exception $e) {
            \Log::warning('Available filters loading failed: '.$e->getMessage());
        }
    }

    public function getResultsCountProperty(): int
    {
        return count($this->results);
    }

    public function getHasResultsProperty(): bool
    {
        return ! empty($this->results);
    }

    public function getIsSearchingProperty(): bool
    {
        return strlen($this->query) >= 2;
    }

    public function getFilterCountProperty(): int
    {
        return count($this->filters);
    }

    public function getSortOptionsProperty(): array
    {
        return [
            'relevance' => __('frontend.sort_by_relevance'),
            'name' => __('frontend.sort_by_name'),
            'price' => __('frontend.sort_by_price'),
            'date' => __('frontend.sort_by_date'),
            'rating' => __('frontend.sort_by_rating'),
        ];
    }

    public function getTypeOptionsProperty(): array
    {
        return [
            'products' => __('frontend.products'),
            'categories' => __('frontend.categories'),
            'brands' => __('frontend.brands'),
            'collections' => __('frontend.collections'),
            'attributes' => __('frontend.attributes'),
            'locations' => __('frontend.locations'),
            'countries' => __('frontend.countries'),
            'cities' => __('frontend.cities'),
            'orders' => __('frontend.orders'),
            'customers' => __('frontend.customers'),
            'addresses' => __('frontend.addresses'),
        ];
    }
}
