<?php

namespace App\Filament\Resources\SystemSettingHistories\Pages;

use App\Filament\Resources\SystemSettingHistories\SystemSettingHistoryResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListSystemSettingHistories extends BaseListRecords
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
