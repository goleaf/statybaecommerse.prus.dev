<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumValueResource\Pages;

use App\Filament\Resources\EnumValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnumValue extends EditRecord
{
    protected static string $resource = EnumValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
