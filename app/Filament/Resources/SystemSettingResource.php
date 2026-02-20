<?php

declare(strict_types=1);

namespace App\Filament\Resources;

/**
 * Backward-compatible alias for historical singular resource naming.
 *
 * This class intentionally overrides getPages() with an empty array so that
 * Filament's resource discovery does not register duplicate routes for the
 * same slug ('system-settings'), which would result in a blank page.
 */
class SystemSettingResource extends SystemSettingsResource
{
    public static function getPages(): array
    {
        return [];
    }
}
