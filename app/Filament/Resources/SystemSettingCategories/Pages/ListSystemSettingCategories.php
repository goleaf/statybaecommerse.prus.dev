<?php

namespace App\Filament\Resources\SystemSettingCategories\Pages;

use App\Filament\Resources\SystemSettingCategories\SystemSettingCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSystemSettingCategories extends ListRecords
{
    protected static string $resource = SystemSettingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
