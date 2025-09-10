<?php declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingResource\Pages;

use App\Filament\Resources\NormalSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewNormalSetting extends ViewRecord
{
    protected static string $resource = NormalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

