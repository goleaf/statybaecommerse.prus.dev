<?php

declare(strict_types=1);

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

final class SystemSettingHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferFilters(false)
            ->searchable()
            ->columns([
                TextColumn::make('systemSetting.key')
                    ->label(__('admin.system_setting_histories.system_setting'))
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('user.name')
                    ->label(__('admin.system_setting_histories.changed_by'))
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                TextColumn::make('change_reason')
                    ->label(__('admin.system_setting_histories.change_reason'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('old_value')
                    ->label(__('admin.system_setting_histories.old_value'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),
                TextColumn::make('new_value')
                    ->label(__('admin.system_setting_histories.new_value'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label(__('admin.system_setting_histories.ip_address'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('system_setting_id')
                    ->label(__('admin.system_setting_histories.system_setting'))
                    ->relationship('systemSetting', 'key', fn ($query) => $query->withoutGlobalScopes())
                    ->searchable(),
                SelectFilter::make('changed_by')
                    ->label(__('admin.system_setting_histories.changed_by'))
                    ->relationship('user', 'name')
                    ->searchable(),
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
                            'type'  => 'string',
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
            ->bulkActions([
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
