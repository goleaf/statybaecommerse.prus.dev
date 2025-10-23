<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\OrderResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

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
}
