<?php

namespace App\Filament\Resources\SystemSettingHistories\Pages;

use App\Filament\Resources\SystemSettingHistories\SystemSettingHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSystemSettingHistories extends ListRecords
{
    protected static string $resource = SystemSettingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
