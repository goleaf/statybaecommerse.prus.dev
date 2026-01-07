<?php

declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingTranslationResource\Pages;

use App\Filament\Resources\NormalSettingTranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditNormalSettingTranslation extends EditRecord
{
    protected static string $resource = NormalSettingTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
