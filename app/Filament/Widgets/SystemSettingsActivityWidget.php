<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSettingHistory;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

final class SystemSettingsActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'admin.system_settings.widgets.settings_activity';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $maxHeight = 400;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SystemSettingHistory::query()
                    ->with(['systemSetting', 'user'])
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('systemSetting.key')
                    ->label(__('admin.system_settings.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.system_settings.key_copied'))
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('systemSetting.name')
                    ->label(__('admin.system_settings.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('old_value')
                    ->label(__('admin.system_settings.old_value'))
                    ->formatStateUsing(fn($state) => $state ? $this->formatHistoryValue($state) : '-')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->getFormattedOldValue();
                    }),
                Tables\Columns\TextColumn::make('new_value')
                    ->label(__('admin.system_settings.new_value'))
                    ->formatStateUsing(fn($state) => $state ? $this->formatHistoryValue($state) : '-')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->getFormattedNewValue();
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.system_settings.changed_by'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_reason')
                    ->label(__('admin.system_settings.change_reason'))
                    ->limit(30)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.system_settings.changed_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(SystemSettingHistory $record): string => route('filament.admin.resources.system-settings.view', $record->systemSetting)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function formatHistoryValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }
}
