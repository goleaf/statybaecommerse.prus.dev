<?php

declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingTranslationResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NormalSettingTranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListNormalSettingTranslations extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = NormalSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
