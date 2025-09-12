<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class RecentSystemSettingsChangesWidget extends BaseWidget
{
    protected static ?string $heading = 'admin.system_settings.widgets.recent_changes';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $maxHeight = 300;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SystemSetting::query()
                    ->latest('updated_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('admin.system_settings.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.system_settings.key_copied'))
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.system_settings.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.system_settings.type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'string', 'text' => 'gray',
                        'number' => 'blue',
                        'boolean' => 'green',
                        'array', 'json' => 'purple',
                        'file', 'image' => 'orange',
                        'select' => 'indigo',
                        'color' => 'pink',
                        'date', 'datetime' => 'yellow',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('group')
                    ->label(__('admin.system_settings.group'))
                    ->badge()
                    ->color('secondary'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.system_settings.category'))
                    ->badge()
                    ->color(fn($record) => $record->category?->color ?? 'primary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.system_settings.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('admin.system_settings.is_public'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_by')
                    ->label(__('admin.system_settings.updated_by'))
                    ->formatStateUsing(fn($state) => $state ? \App\Models\User::find($state)?->name : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.system_settings.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(SystemSetting $record): string => route('filament.admin.resources.system-settings.view', $record)),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
