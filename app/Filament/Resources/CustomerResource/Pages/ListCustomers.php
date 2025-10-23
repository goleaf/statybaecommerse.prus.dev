<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Widgets\CustomerGrowthChart;
use App\Filament\Resources\CustomerResource\Widgets\CustomerResourceStats;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListCustomers extends BaseListRecords
{
    use HasResizableColumns;

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
}
