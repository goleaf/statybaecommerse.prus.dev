<?php declare(strict_types=1);

namespace App\Filament\Resources\UserWishlistResource\Pages;

use App\Filament\Resources\UserWishlistResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListUserWishlists extends ListRecords
{
    protected static string $resource = UserWishlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

