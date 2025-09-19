<?php

namespace App\Filament\Resources\SystemSettingHistories\Pages;

use App\Filament\Resources\SystemSettingHistories\SystemSettingHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSystemSettingHistory extends EditRecord
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
