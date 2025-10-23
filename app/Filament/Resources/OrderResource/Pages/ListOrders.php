<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderResourceStats;
use App\Filament\Resources\OrderResource\Widgets\OrderRevenueTrend;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class ListOrders extends BaseListRecords
{
    use SpatieTranslatableListRecords; // Track the active locale for listing translated records.

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        if (! OrderResource::canCreate()) {
            return [];
        }

        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('orders', 'create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderResourceStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            OrderRevenueTrend::class,
        ];
    }
}
