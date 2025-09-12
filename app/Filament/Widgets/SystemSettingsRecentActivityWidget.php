<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use App\Models\SystemSettingHistory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class SystemSettingsRecentActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent System Settings Activity';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SystemSettingHistory::query()
                    ->with(['systemSetting', 'changedBy'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('systemSetting.name')
                    ->label(__('admin.system_settings.setting_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('systemSetting.key')
                    ->label(__('admin.system_settings.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label(__('admin.system_settings.changed_by'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_type')
                    ->label(__('admin.system_settings.change_type'))
                    ->formatStateUsing(fn (SystemSettingHistory $record) => $record->getChangeTypeLabel())
                    ->badge()
                    ->color(fn (SystemSettingHistory $record) => match ($record->getChangeType()) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('old_value')
                    ->label(__('admin.system_settings.old_value'))
                    ->formatStateUsing(fn (SystemSettingHistory $record) => $record->getFormattedOldValue())
                    ->limit(20)
                    ->tooltip(fn (SystemSettingHistory $record) => $record->getFormattedOldValue()),

                Tables\Columns\TextColumn::make('new_value')
                    ->label(__('admin.system_settings.new_value'))
                    ->formatStateUsing(fn (SystemSettingHistory $record) => $record->getFormattedNewValue())
                    ->limit(20)
                    ->tooltip(fn (SystemSettingHistory $record) => $record->getFormattedNewValue()),

                Tables\Columns\TextColumn::make('change_reason')
                    ->label(__('admin.system_settings.reason'))
                    ->limit(30)
                    ->tooltip(fn (SystemSettingHistory $record) => $record->change_reason),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.system_settings.changed_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
