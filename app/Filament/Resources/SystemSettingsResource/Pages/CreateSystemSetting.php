<?php declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Pages;

use App\Filament\Resources\SystemSettingsResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSystemSetting extends CreateRecord
{
    protected static string $resource = SystemSettingsResource::class;

    public function getTitle(): string
    {
        return __('Create Setting');
    }
}
