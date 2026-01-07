<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    /**
     * @param int|string $record
     */
    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record instanceof Notification && ! $this->record->getAttribute('is_read')) {
            $this->record->markAsRead();
            $this->record->refresh();
        }
    }
}
