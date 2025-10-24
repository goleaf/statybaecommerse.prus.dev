<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Components\Section as BaseSection;

/**
 * Bridge class to preserve compatibility with existing Filament form schemas
 * that reference `Section` without the fully qualified namespace.
 */
class Section extends BaseSection
{
}
