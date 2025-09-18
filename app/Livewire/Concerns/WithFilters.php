<?php

declare (strict_types=1);
namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
/**
 * WithFilters
 * 
 * Trait providing reusable functionality across multiple classes.
 */
trait WithFilters
{
    use WithPagination;
    #[Url]
    public string $search = '';
    #[Url]
    public array $selectedCategories = [];
    #[Url]
    public array $selectedBrands = [];
    #[Url]
    public string $sortBy = 'name';
    #[Url]
    public string $sortDirection = 'asc';
    #[Url]
    public int $priceMin = 0;
    #[Url]
    public int $priceMax = 10000;
    #[Url]
    public int $minPrice = 0;
    #[Url]
    public int $maxPrice = 1000;
    #[Url]
    public int $priceRange = 1000;
    #[Url]
    public string $availability = 'all';
    #[Url]
    public int $perPage = 12;
    public bool $showFilters = false;
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedSelectedCategories(): void
    {
        $this->resetPage();
    }
    public function updatedSelectedBrands(): void
    {
        $this->resetPage();
    }
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }
    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }
    public function updatedPriceMin(): void
    {
        $this->resetPage();
    }
    public function updatedPriceMax(): void
    {
        $this->resetPage();
    }
    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }
    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }
    public function updatedPriceRange(): void
    {
        $this->maxPrice = $this->priceRange;
        $this->resetPage();
    }
    public function updatedAvailability(): void
    {
        $this->resetPage();
    }
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
    public function clearFilters(): void
    {
        $this->reset(['search', 'selectedCategories', 'selectedBrands', 'priceMin', 'priceMax', 'minPrice', 'maxPrice', 'priceRange', 'availability']);
        $this->resetPage();
    }
    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }
    protected function applySearchFilters(Builder $query): Builder
    {
        return $query->when($this->search, function (Builder $q) {
            $q->where(function (Builder $subQuery) {
                $subQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%')->orWhere('sku', 'like', '%' . $this->search . '%')->orWhereHas('brand', function (Builder $brandQuery) {
                    $brandQuery->where('name', 'like', '%' . $this->search . '%');
                });
            });
        })->when($this->selectedCategories, function (Builder $q) {
            $q->whereHas('categories', function (Builder $categoryQuery) {
                $categoryQuery->whereIn('categories.id', $this->selectedCategories);
            });
        })->when($this->selectedBrands, function (Builder $q) {
            $q->whereIn('brand_id', $this->selectedBrands);
        })->when($this->priceMin > 0, function (Builder $q) {
            $q->where('price', '>=', $this->priceMin);
        })->when($this->priceMax < 10000, function (Builder $q) {
            $q->where('price', '<=', $this->priceMax);
        })->when($this->minPrice > 0, function (Builder $q) {
            $q->where('price', '>=', $this->minPrice);
        })->when($this->maxPrice < 1000, function (Builder $q) {
            $q->where('price', '<=', $this->maxPrice);
        })->when($this->availability === 'in_stock', function (Builder $q) {
            $q->where('stock_quantity', '>', 0);
        })->when($this->availability === 'out_of_stock', function (Builder $q) {
            $q->where('stock_quantity', '<=', 0);
        });
    }
    protected function applySorting(Builder $query): Builder
    {
        // Map UI sort keys to safe columns/aggregates
        $sortKey = $this->sortBy;
        $direction = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';
        $whitelist = ['name' => 'name', 'price' => 'price', 'created_at' => 'created_at'];
        if ($sortKey === 'popularity') {
            // Requires withCount('orderItems') in query
            return $query->orderBy('order_items_count', $direction);
        }
        if ($sortKey === 'rating') {
            // Requires withAvg('reviews as average_rating', 'rating') in query
            return $query->orderBy('average_rating', $direction);
        }
        $column = $whitelist[$sortKey] ?? 'created_at';
        return $query->orderBy($column, $direction);
    }
}