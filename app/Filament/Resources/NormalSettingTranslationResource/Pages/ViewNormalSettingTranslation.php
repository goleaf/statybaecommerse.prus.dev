<?php

declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingTranslationResource\Pages;

use App\Filament\Resources\NormalSettingTranslationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewNormalSettingTranslation extends ViewRecord
{
    protected static string $resource = NormalSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
