<?php declare(strict_types=1);

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

trait WithFilters
{
    use WithPagination;

    public string $search = '';
    public array $selectedCategories = [];
    public array $selectedBrands = [];
    public array $selectedAttributes = [];
    public ?float $minPrice = null;
    public ?float $maxPrice = null;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public bool $inStock = false;
    public bool $onSale = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategories' => ['except' => []],
        'selectedBrands' => ['except' => []],
        'selectedAttributes' => ['except' => []],
        'minPrice' => ['except' => null],
        'maxPrice' => ['except' => null],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'inStock' => ['except' => false],
        'onSale' => ['except' => false],
    ];

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

    public function updatedSelectedAttributes(): void
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

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function updatedInStock(): void
    {
        $this->resetPage();
    }

    public function updatedOnSale(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'selectedCategories',
            'selectedBrands',
            'selectedAttributes',
            'minPrice',
            'maxPrice',
            'inStock',
            'onSale',
        ]);
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
        $this->dispatch('filters-applied');
    }

    protected function applySearchFilters($query)
    {
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('summary', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedCategories)) {
            $query->whereHas('categories', function ($q) {
                $q->whereIn('categories.id', $this->selectedCategories);
            });
        }

        if (!empty($this->selectedBrands)) {
            $query->whereIn('brand_id', $this->selectedBrands);
        }

        if ($this->minPrice !== null) {
            $query->whereHas('prices', function ($q) {
                $q->where('amount', '>=', $this->minPrice);
            });
        }

        if ($this->maxPrice !== null) {
            $query->whereHas('prices', function ($q) {
                $q->where('amount', '<=', $this->maxPrice);
            });
        }

        if ($this->inStock) {
            $query->where(function ($q) {
                $q->whereNull('stock_quantity')
                  ->orWhere('stock_quantity', '>', 0);
            });
        }

        if ($this->onSale) {
            $query->whereNotNull('sale_price')
                  ->where('sale_price', '>', 0);
        }

        return $query;
    }

    protected function applySorting($query)
    {
        match ($this->sortBy) {
            'name' => $query->orderBy('name', $this->sortDirection),
            'price' => $query->orderBy('price', $this->sortDirection),
            'created_at' => $query->orderBy('created_at', $this->sortDirection),
            'updated_at' => $query->orderBy('updated_at', $this->sortDirection),
            default => $query->orderBy('created_at', $this->sortDirection),
        };

        return $query;
    }
}
