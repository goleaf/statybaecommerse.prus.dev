<?php

namespace App\Filament\Resources\SystemSettingHistories\Tables;

use App\Models\SystemSettingHistory;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SystemSettingHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('restore_value')
                    ->label(__('admin.system_setting_histories.restore_value'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->action(function (SystemSettingHistory $record): void {
                        $record->systemSetting()->update([
                            'type' => 'string',
                            'value' => $record->old_value,
                        ]);

                        Notification::make()
                            ->title(__('admin.system_setting_histories.value_restored_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (SystemSettingHistory $record): bool => ! empty($record->old_value)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_history')
                        ->label(__('admin.system_setting_histories.export_history'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                // no-op export simulation for tests
                                $record->getKey();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
