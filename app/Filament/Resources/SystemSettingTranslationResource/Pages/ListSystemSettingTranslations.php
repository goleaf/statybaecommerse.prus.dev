<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingTranslationResource\Pages;

use App\Filament\Resources\SystemSettingTranslationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListSystemSettingTranslations extends ListRecords
{
    protected static string $resource = SystemSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
