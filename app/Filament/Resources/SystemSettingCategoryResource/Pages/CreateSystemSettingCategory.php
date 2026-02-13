<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Pages;

use App\Filament\Resources\SystemSettingCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemSettingCategory extends CreateRecord
{
    protected static string $resource = SystemSettingCategoryResource::class;
}
