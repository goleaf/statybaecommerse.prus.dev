<?php declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;

/**
 * Backwards-compatibility bridge: makes EnhancedSettingResource resolve to the
 * same routes and URLs as NormalSettingResource without inheritance.
 */
final class EnhancedSettingResource extends Resource
{
    public static function getSlug(?Panel $panel = null): string
    {
        return 'normal-settings';
    }

    public static function getUrl(
        ?string $name = null,
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
    ): string {
        return NormalSettingResource::getUrl(
            $name,
            $parameters,
            $isAbsolute,
            $panel,
            $tenant,
            $shouldGuessMissingParameters,
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
