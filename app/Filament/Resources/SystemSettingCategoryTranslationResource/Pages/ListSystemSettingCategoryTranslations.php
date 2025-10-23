<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemSettingCategoryTranslationResource;
use Filament\Actions\CreateAction;

final class ListSystemSettingCategoryTranslations extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SystemSettingCategoryTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
