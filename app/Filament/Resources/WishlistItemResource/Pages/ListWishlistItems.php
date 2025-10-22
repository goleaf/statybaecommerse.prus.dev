<?php

declare(strict_types=1);

namespace App\Filament\Resources\WishlistItemResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\WishlistItemResource;
use Filament\Actions;

class ListWishlistItems extends BaseListRecords
{
    protected static string $resource = WishlistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
