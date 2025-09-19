<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantAttributeValueResource\Pages;

use App\Filament\Resources\VariantAttributeValueResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditVariantAttributeValue extends EditRecord
{
    protected static string $resource = VariantAttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

