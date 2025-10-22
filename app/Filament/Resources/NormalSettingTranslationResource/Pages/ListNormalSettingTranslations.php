<?php

declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingTranslationResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NormalSettingTranslationResource;
use Filament\Actions;

final class ListNormalSettingTranslations extends BaseListRecords
{
    protected static string $resource = NormalSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
