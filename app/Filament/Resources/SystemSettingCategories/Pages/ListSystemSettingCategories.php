<?php

namespace App\Filament\Resources\SystemSettingCategories\Pages;

use App\Filament\Resources\SystemSettingCategories\SystemSettingCategoryResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListSystemSettingCategories extends BaseListRecords
{
    protected static string $resource = SystemSettingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
