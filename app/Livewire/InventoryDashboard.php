<?php

declare (strict_types=1);
namespace App\Livewire;

use App\Services\InventoryService;
use Livewire\Component;
/**
 * InventoryDashboard
 * 
 * Livewire component for InventoryDashboard with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property array $summary
 * @property array $lowStockProducts
 * @property array $outOfStockProducts
 */
final class InventoryDashboard extends Component
{
    public array $summary = [];
    public array $lowStockProducts = [];
    public array $outOfStockProducts = [];
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->loadData();
    }
    /**
     * Handle loadData functionality with proper error handling.
     * @return void
     */
    public function loadData(): void
    {
        $inventoryService = app(InventoryService::class);
        $this->summary = $inventoryService->getInventorySummary();
        $this->lowStockProducts = $inventoryService->getLowStockProducts(5)->toArray();
        $this->outOfStockProducts = $inventoryService->getOutOfStockProducts(5)->toArray();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.inventory-dashboard');
    }
}