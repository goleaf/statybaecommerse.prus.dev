<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ActivityLogResource;

final class ListActivityLogs extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ActivityLogResource::class;

    public function mount(): void
    {
        parent::mount();

        // Ensure the activity log table hydrates immediately so assertions and the UI
        // can inspect the initial dataset without requiring an explicit loadTable() call.
        $this->loadTable();

        // The explicit flag is kept for clarity even though loadTable() currently only toggles it.
        $this->isTableLoaded = true;
    }
}
