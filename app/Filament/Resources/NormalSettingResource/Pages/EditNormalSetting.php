<?php declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingResource\Pages;

use App\Filament\Resources\NormalSettingResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditNormalSetting extends EditRecord
{
    protected static string $resource = NormalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

