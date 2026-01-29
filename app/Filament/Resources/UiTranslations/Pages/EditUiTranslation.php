<?php

declare(strict_types=1);

namespace App\Filament\Resources\UiTranslations\Pages;

use App\Filament\Resources\UiTranslations\UiTranslationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUiTranslation extends EditRecord
{
    protected static string $resource = UiTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
