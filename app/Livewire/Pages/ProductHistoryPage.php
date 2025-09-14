<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\ProductHistory;
use Livewire\Component;
use Livewire\WithPagination;

final /**
 * ProductHistory
 * 
 * Livewire component for reactive frontend functionality.
 */
class ProductHistoryPage extends Component
{
    use WithPagination;

    public Product $product;

    public int $perPage = 20;
    public string $actionFilter = '';
    public string $dateFilter = '';

    protected $listeners = ['refreshHistory' => '$refresh'];

    protected $queryString = [
        'perPage' => ['except' => 20],
        'actionFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];


    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function getHistoryProperty()
    {
        $query = $this->product->histories()
            ->with(['user:id,name,email'])
            ->latest();

        // Apply action filter
        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        // Apply date filter
        if ($this->dateFilter) {
            $query->where('created_at', '>=', now()->subDays((int) $this->dateFilter));
        }

        return $query->paginate($this->perPage);
    }

    public function getTotalChangesProperty(): int
    {
        return $this->product->histories()->count();
    }

    public function getPriceChangesProperty(): int
    {
        return $this->product->priceHistories()->count();
    }

    public function getStockUpdatesProperty(): int
    {
        return $this->product->stockHistories()->count();
    }

    public function getLastChangeProperty(): ?ProductHistory
    {
        return $this->product->histories()->latest()->first();
    }

    public function render()
    {
        return view('livewire.pages.product-history', [
            'history' => $this->history,
            'product' => $this->product,
            'totalChanges' => $this->totalChanges,
            'priceChanges' => $this->priceChanges,
            'stockUpdates' => $this->stockUpdates,
            'lastChange' => $this->lastChange,
        ])->layout('layouts.app', [
            'title' => __('frontend.products.history_title', ['product' => $this->product->trans('name') ?? $this->product->name]),
        ]);
    }
}
