<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Resources\ProductImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditProductImage extends EditRecord
{
    protected static string $resource = ProductImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
