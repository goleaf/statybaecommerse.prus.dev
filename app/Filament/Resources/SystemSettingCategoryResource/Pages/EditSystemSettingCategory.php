<?php declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Pages;

use App\Filament\Resources\SystemSettingCategoryResource;
use Filament\Resources\Pages\EditRecord;

final class EditSystemSettingCategory extends EditRecord
{
    protected static string $resource = SystemSettingCategoryResource::class;
}
