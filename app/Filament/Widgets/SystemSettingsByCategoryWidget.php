<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSettingCategory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final /**
 * SystemSettingsByCategoryWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class SystemSettingsByCategoryWidget extends BaseWidget
{
    protected static ?string $heading = 'System Settings by Category';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SystemSettingCategory::query()
                    ->withCount(['settings as total_settings'])
                    ->withCount(['settings as active_settings' => function ($query) {
                        $query->where('is_active', true);
                    }])
                    ->withCount(['settings as public_settings' => function ($query) {
                        $query->where('is_active', true)->where('is_public', true);
                    }])
                    ->active()
                    ->ordered()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.system_settings.category_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.system_settings.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('total_settings')
                    ->label(__('admin.system_settings.total_settings'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('active_settings')
                    ->label(__('admin.system_settings.active_settings'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color('success'),

                Tables\Columns\TextColumn::make('public_settings')
                    ->label(__('admin.system_settings.public_settings'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color('info'),

                Tables\Columns\IconColumn::make('icon')
                    ->label(__('admin.system_settings.icon'))
                    ->formatStateUsing(fn (SystemSettingCategory $record) => $record->getIconClass())
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('color')
                    ->label(__('admin.system_settings.color'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'primary' => 'primary',
                        'secondary' => 'secondary',
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                        'info' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.system_settings.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.system_settings.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (SystemSettingCategory $record) => route('filament.admin.resources.system-settings.index', [
                        'tableFilters' => [
                            'category_id' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])),
            ])
            ->defaultSort('sort_order')
            ->paginated(false);
    }
}
