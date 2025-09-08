<?php declare(strict_types=1);

namespace App\Filament\Resources\EnhancedSettingResource\Pages;

use App\Filament\Resources\EnhancedSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewEnhancedSetting extends ViewRecord
{
    protected static string $resource = EnhancedSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
