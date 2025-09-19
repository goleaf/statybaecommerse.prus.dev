<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages;

use App\Filament\Resources\SystemSettingCategoryTranslationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListSystemSettingCategoryTranslations extends ListRecords
{
    protected static string $resource = SystemSettingCategoryTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
