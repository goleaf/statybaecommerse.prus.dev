<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserWishlistResource\Pages;

use App\Filament\Resources\UserWishlistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditUserWishlist extends EditRecord
{
    protected static string $resource = UserWishlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
