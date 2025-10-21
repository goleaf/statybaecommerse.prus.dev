<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Asmit\ResizedColumn\HasResizableColumn as BaseHasResizableColumn;
use Filament\Facades\Filament;

/**
 * Decorates the vendor resizable column trait so width persistence is scoped to the admin panel guard.
 */
trait HasResizableColumns
{
    use BaseHasResizableColumn {
        persistColumnWidthsToDatabase as private basePersistColumnWidthsToDatabase;
        loadColumnWidthsFromDatabase as private baseLoadColumnWidthsFromDatabase;
        getSessionKey as private baseGetSessionKey;
    }

    /**
     * Resolve the authenticated admin identifier from the Filament panel guard.
     */
    protected function getUserId(): int|string|null
    {
        // Always query the configured Filament panel so table widths are bound to the expected guard.
        return Filament::getPanel(static::getResizableColumnPanelId())?->auth()?->id();
    }

    /**
     * Guard database persistence so anonymous sessions never write empty rows.
     */
    protected function persistColumnWidthsToDatabase(): void
    {
        if ($this->getUserId() === null) {
            // Skip writes when the guard has no authenticated admin.
            return;
        }

        $this->basePersistColumnWidthsToDatabase();
    }

    /**
     * Prevent database lookups when no admin guard is active.
     */
    protected function loadColumnWidthsFromDatabase(): void
    {
        if ($this->getUserId() === null) {
            // Clear any stale widths so the table renders with default sizing for guests.
            $this->columnWidths = [];

            return;
        }

        $this->baseLoadColumnWidthsFromDatabase();
    }

    /**
     * Append the authenticated admin identifier to the session key to avoid guard collisions.
     */
    protected function getSessionKey(): string
    {
        $sessionKey = $this->baseGetSessionKey();
        $userId = $this->getUserId();

        // Only scope the key when an admin is available—guests keep the vanilla namespace.
        if ($userId === null) {
            return $sessionKey;
        }

        return sprintf('%s_%s_%s', $sessionKey, static::getResizableColumnPanelId(), $userId);
    }

    /**
     * Expose the Filament panel identifier used for guard resolution.
     */
    protected static function getResizableColumnPanelId(): string
    {
        // The admin panel is the default consumer; downstream panels can override the method to opt into their guard.
        return 'admin';
    }
}
