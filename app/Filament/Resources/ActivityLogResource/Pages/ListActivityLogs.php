<?php declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

final class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('admin.activity_logs.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->redirect(request()->url());
                }),

            Action::make('export')
                ->label(__('admin.activity_logs.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    // Export logic would go here
                    Notification::make()
                        ->title(__('admin.activity_logs.messages.export_success'))
                        ->success()
                        ->send();
                }),

            Action::make('clear_old_logs')
                ->label(__('admin.clear_old_logs'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('admin.clear_old_logs_confirm'))
                ->modalDescription(__('admin.clear_old_logs_description'))
                ->action(function () {
                    $deletedCount = DB::table('activity_log')
                        ->where('created_at', '<', now()->subDays(30))
                        ->delete();

                    Notification::make()
                        ->title(__('admin.logs_cleared', ['count' => $deletedCount]))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActivityLogResource\Widgets\ActivityLogStatsWidget::class,
        ];
    }
}
