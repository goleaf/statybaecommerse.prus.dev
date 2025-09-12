<?php declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_read')
                ->label(__('admin.actions.mark_read'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->record->markAsRead();
                })
                ->visible(fn () => $this->record->read_at === null),
            Actions\DeleteAction::make(),
        ];
    }
}