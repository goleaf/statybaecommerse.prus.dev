<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderResourceStats;
use App\Filament\Resources\OrderResource\Widgets\OrderRevenueTrend;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListOrders extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        if (! OrderResource::canCreate()) {
            return [];
        }

        return [
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
