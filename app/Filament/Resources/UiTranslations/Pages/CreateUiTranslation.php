<?php

declare(strict_types=1);

namespace App\Filament\Resources\UiTranslations\Pages;

use App\Filament\Resources\UiTranslations\UiTranslationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUiTranslation extends CreateRecord
{
    protected static string $resource = UiTranslationResource::class;
}
