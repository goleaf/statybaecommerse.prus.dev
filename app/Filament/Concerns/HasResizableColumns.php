<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Facades\Filament;

trait HasResizableColumns
{
    use HasResizableColumn {
        getUserId as protected getDefaultResizableColumnUserId;
    }

    protected function getUserId(): int|string|null
    {
        $auth = Filament::auth();

        return $auth?->id() ?? $this->getDefaultResizableColumnUserId();
    }
}
