<?php

declare(strict_types=1);

namespace App\Filament\Resources;

/**
 * @deprecated use SystemSettingResource instead.
 *
 * Provide a class_alias so legacy references continue working without
 * attempting to inherit from the final SystemSettingResource class.
 */
class_alias(SystemSettingResource::class, __NAMESPACE__ . '\\SystemSettingsResource');
