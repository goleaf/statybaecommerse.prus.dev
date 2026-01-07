<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditAttributeValue extends EditRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
