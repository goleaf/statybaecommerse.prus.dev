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
            Actions\Action::make('generate_sample')
                ->label(__('Generate Sample Notifications'))
                ->icon('heroicon-o-plus')
                ->color('success')
                ->action(function () {
                    $this->runCommand('notifications:generate-sample', ['--count' => 20]);
                })
                ->requiresConfirmation()
                ->modalHeading(__('Generate Sample Notifications'))
                ->modalDescription(__('This will generate 20 sample notifications for testing purposes.'))
                ->modalSubmitActionLabel(__('Generate')),
                
            Actions\Action::make('clear_all')
                ->label(__('Clear All Notifications'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->action(function () {
                    \Illuminate\Notifications\DatabaseNotification::truncate();
                    $this->notify('success', __('All notifications have been cleared.'));
                })
                ->requiresConfirmation()
                ->modalHeading(__('Clear All Notifications'))
                ->modalDescription(__('This will permanently delete all notifications. This action cannot be undone.'))
                ->modalSubmitActionLabel(__('Clear All')),
        ];
    }

    private function runCommand(string $command, array $arguments = []): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call($command, $arguments);
        
        if ($exitCode === 0) {
            $this->notify('success', __('Sample notifications generated successfully.'));
        } else {
            $this->notify('danger', __('Failed to generate sample notifications.'));
        }
    }
}