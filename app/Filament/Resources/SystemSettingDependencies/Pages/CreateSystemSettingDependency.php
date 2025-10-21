<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencies\Pages;

use App\Filament\Resources\SystemSettingDependencies\SystemSettingDependencyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemSettingDependency extends CreateRecord
{
    protected static string $resource = SystemSettingDependencyResource::class;
}
