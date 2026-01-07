<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingTranslationResource\Pages;

use App\Filament\Resources\SystemSettingTranslationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSystemSettingTranslation extends CreateRecord
{
    protected static string $resource = SystemSettingTranslationResource::class;
}
