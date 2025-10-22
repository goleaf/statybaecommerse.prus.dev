<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemSettingCategoryResource;
use Filament\Actions;

class ListSystemSettingCategories extends BaseListRecords
{
    protected static string $resource = SystemSettingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
