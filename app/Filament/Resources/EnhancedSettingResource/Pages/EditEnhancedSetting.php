<?php declare(strict_types=1);

namespace App\Filament\Resources\EnhancedSettingResource\Pages;

use App\Filament\Resources\EnhancedSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditEnhancedSetting extends EditRecord
{
    protected static string $resource = EnhancedSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}