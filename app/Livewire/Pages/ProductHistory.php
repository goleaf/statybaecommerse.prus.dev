<?php

declare (strict_types=1);
namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\ProductHistory;
use Livewire\Component;
use Livewire\WithPagination;
/**
 * ProductHistoryPage
 * 
 * Livewire component for ProductHistoryPage with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Product $product
 * @property int $perPage
 * @property string $actionFilter
 * @property string $dateFilter
 * @property mixed $listeners
 * @property mixed $queryString
 */
final class ProductHistoryPage extends Component
{
    use WithPagination;
    public Product $product;
    public int $perPage = 20;
    public string $actionFilter = '';
    public string $dateFilter = '';
    protected $listeners = ['refreshHistory' => '$refresh'];
    protected $queryString = ['perPage' => ['except' => 20], 'actionFilter' => ['except' => ''], 'dateFilter' => ['except' => '']];
    /**
     * Handle __invoke functionality with proper error handling.
     * @param Product $product
     */
    public function __invoke(Product $product)
    {
        $this->mount($product);
        return $this->render();
    }
    /**
     * Initialize the Livewire component with parameters.
     * @param Product $product
     * @return void
     */
    public function mount(Product $product): void
    {
        $this->product = $product;
    }
    /**
     * Handle updatedPerPage functionality with proper error handling.
     * @return void
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedActionFilter functionality with proper error handling.
     * @return void
     */
    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedDateFilter functionality with proper error handling.
     * @return void
     */
    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }
    /**
     * Handle getHistoryProperty functionality with proper error handling.
     */
    public function getHistoryProperty()
    {
        $query = $this->product->histories()->with(['user:id,name,email'])->latest();
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
    /**
     * Handle getTotalChangesProperty functionality with proper error handling.
     * @return int
     */
    public function getTotalChangesProperty(): int
    {
        return $this->product->histories()->count();
    }
    /**
     * Handle getPriceChangesProperty functionality with proper error handling.
     * @return int
     */
    public function getPriceChangesProperty(): int
    {
        return $this->product->priceHistories()->count();
    }
    /**
     * Handle getStockUpdatesProperty functionality with proper error handling.
     * @return int
     */
    public function getStockUpdatesProperty(): int
    {
        return $this->product->stockHistories()->count();
    }
    /**
     * Handle getLastChangeProperty functionality with proper error handling.
     * @return ProductHistory|null
     */
    public function getLastChangeProperty(): ?ProductHistory
    {
        return $this->product->histories()->latest()->first();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.product-history', ['history' => $this->history, 'product' => $this->product, 'totalChanges' => $this->totalChanges, 'priceChanges' => $this->priceChanges, 'stockUpdates' => $this->stockUpdates, 'lastChange' => $this->lastChange])->layout('layouts.app', ['title' => __('frontend.products.history_title', ['product' => $this->product->trans('name') ?? $this->product->name])]);
    }
}