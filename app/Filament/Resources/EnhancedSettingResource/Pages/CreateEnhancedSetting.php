<?php declare(strict_types=1);

namespace App\Filament\Resources\EnhancedSettingResource\Pages;

use App\Filament\Resources\EnhancedSettingResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEnhancedSetting extends CreateRecord
{
    protected static string $resource = EnhancedSettingResource::class;
}