<?php declare(strict_types=1);

namespace App\Filament\Resources\WishlistItemResource\Pages;

use App\Filament\Resources\WishlistItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWishlistItem extends EditRecord
{
    protected static string $resource = WishlistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
