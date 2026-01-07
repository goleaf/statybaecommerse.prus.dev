<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages;

use App\Filament\Resources\SystemSettingCategoryTranslationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewSystemSettingCategoryTranslation extends ViewRecord
{
    protected static string $resource = SystemSettingCategoryTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
