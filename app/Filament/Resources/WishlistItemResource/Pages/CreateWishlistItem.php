<?php declare(strict_types=1);

namespace App\Filament\Resources\WishlistItemResource\Pages;

use App\Filament\Resources\WishlistItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWishlistItem extends CreateRecord
{
    protected static string $resource = WishlistItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
