<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingTranslationResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SystemSettingTranslationResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

final class ListSystemSettingTranslations extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SystemSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
