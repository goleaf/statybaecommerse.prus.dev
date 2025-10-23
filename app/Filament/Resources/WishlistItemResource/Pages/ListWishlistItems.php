<?php

declare(strict_types=1);

namespace App\Filament\Resources\WishlistItemResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\WishlistItemResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

class ListWishlistItems extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = WishlistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
