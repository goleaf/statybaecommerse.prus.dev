<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistoryResource\Pages;

use App\Filament\Resources\SystemSettingHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditSystemSettingHistory extends EditRecord
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
