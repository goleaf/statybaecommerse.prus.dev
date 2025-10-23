<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserWishlistResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\UserWishlistResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListUserWishlists extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = UserWishlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
