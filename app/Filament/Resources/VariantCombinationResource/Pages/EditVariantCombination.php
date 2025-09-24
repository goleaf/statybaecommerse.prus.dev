<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantCombinationResource\Pages;

use App\Filament\Resources\VariantCombinationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditVariantCombination extends EditRecord
{
    protected static string $resource = VariantCombinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
