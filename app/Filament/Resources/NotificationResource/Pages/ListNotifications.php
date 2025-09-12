<?php declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_all_read')
                ->label(__('admin.actions.mark_all_read'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    auth()->user()->unreadNotifications->markAsRead();
                })
                ->requiresConfirmation(),
        ];
    }
}