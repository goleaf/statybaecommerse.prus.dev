<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingTranslationResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemSettingTranslationResource;
use Filament\Actions\CreateAction;

final class ListSystemSettingTranslations extends BaseListRecords
{
    protected static string $resource = SystemSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
