<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingTranslationResource\Pages;

use App\Filament\Resources\SystemSettingTranslationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditSystemSettingTranslation extends EditRecord
{
    protected static string $resource = SystemSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
