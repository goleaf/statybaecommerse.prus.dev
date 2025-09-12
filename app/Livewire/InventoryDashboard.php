<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\InventoryService;
use Livewire\Component;

final class InventoryDashboard extends Component
{
    public array $summary = [];

    public array $lowStockProducts = [];

    public array $outOfStockProducts = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $inventoryService = app(InventoryService::class);

        $this->summary = $inventoryService->getInventorySummary();
        $this->lowStockProducts = $inventoryService->getLowStockProducts(5)->toArray();
        $this->outOfStockProducts = $inventoryService->getOutOfStockProducts(5)->toArray();
    }

    public function render()
    {
        return view('livewire.inventory-dashboard');
    }
}
