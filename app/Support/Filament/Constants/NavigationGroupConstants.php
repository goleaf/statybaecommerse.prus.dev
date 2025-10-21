<?php

declare(strict_types=1);

namespace App\Support\Filament\Constants;

/**
 * NavigationGroupConstants
 *
 * Central location for tooling-related constants so console commands
 * and standalone scripts stay in sync when normalizing Filament
 * navigation group declarations.
 */
final class NavigationGroupConstants
{
    /**
     * Shared "use" import statement for UnitEnum so regex replacements
     * in tooling scripts avoid hard-coded literals across files.
     */
    public const UNIT_ENUM_USE = 'use UnitEnum;';

    private function __construct()
    {
        // Prevent instantiation because this class only exposes constants.
    }

    /**
     * Provide a reusable, delimiter-safe regex fragment for the UnitEnum
     * import that all tooling consumers can share.
     */
    public static function unitEnumImportPattern(): string
    {
        return preg_quote(self::UNIT_ENUM_USE, '/');
    }
}
