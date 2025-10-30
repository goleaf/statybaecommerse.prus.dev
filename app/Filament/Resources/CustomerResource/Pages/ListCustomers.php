<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Widgets\CustomerGrowthChart;
use App\Filament\Resources\CustomerResource\Widgets\CustomerResourceStats;
use Filament\Actions;

final class ListCustomers extends BaseListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerResourceStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CustomerGrowthChart::class,
        ];
    }

    /**
     * Explicitly expose the `loadTable` hook so Livewire tests that call the
     * method directly can hydrate the deferred table without relying on parent
     * reflection quirks introduced in newer Livewire releases.
     */
    public function loadTable(): void
    {
        parent::loadTable();
    }
}
