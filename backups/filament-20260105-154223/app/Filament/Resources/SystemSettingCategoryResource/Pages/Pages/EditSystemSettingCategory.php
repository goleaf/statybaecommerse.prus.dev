<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Pages;

use App\Filament\Resources\SystemSettingCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSystemSettingCategory extends EditRecord
{
    protected static string $resource = SystemSettingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
