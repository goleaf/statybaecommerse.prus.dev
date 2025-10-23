<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategories\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SystemSettingCategories\SystemSettingCategoryResource;
use Filament\Actions\CreateAction;

class ListSystemSettingCategories extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SystemSettingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
