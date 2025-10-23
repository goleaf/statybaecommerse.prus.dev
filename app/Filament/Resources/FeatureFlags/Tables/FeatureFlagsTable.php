<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Tables;

use App\Models\FeatureFlag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeatureFlagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('key')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('environment')
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_enabled')
                    ->boolean(),
                IconColumn::make('is_global')
                    ->boolean(),
                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('priority')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('impact_level')
                    ->searchable(),
                TextColumn::make('rollout_strategy')
                    ->searchable(),
                TextColumn::make('approval_status')
                    ->searchable(),
                TextColumn::make('created_by_display')
                    ->label(__('system.created_by'))
                    ->formatStateUsing(fn (FeatureFlag $record): string => $record->created_by_display ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $scopedQuery) use ($search): void {
                            $scopedQuery
                                ->whereHas('creator', fn (Builder $creatorQuery): Builder => $creatorQuery->where('name', 'like', "%{$search}%"))
                                ->orWhere('created_by_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('updated_by_display')
                    ->label(__('system.updated_by'))
                    ->formatStateUsing(fn (FeatureFlag $record): string => $record->updated_by_display ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $scopedQuery) use ($search): void {
                            $scopedQuery
                                ->whereHas('updater', fn (Builder $updaterQuery): Builder => $updaterQuery->where('name', 'like', "%{$search}%"))
                                ->orWhere('updated_by_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('last_activated')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_deactivated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('feature_flags.category'))
                    ->options([
                        'ui'          => 'UI/UX',
                        'performance' => 'Performance',
                        'security'    => 'Security',
                        'analytics'   => 'Analytics',
                        'payment'     => 'Payment',
                        'shipping'    => 'Shipping',
                    ]),
                SelectFilter::make('environment')
                    ->label(__('feature_flags.environment'))
                    ->options([
                        'local'      => 'Local',
                        'staging'    => 'Staging',
                        'production' => 'Production',
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('feature_flags.is_active')),
                TernaryFilter::make('is_enabled')
                    ->label(__('feature_flags.is_enabled')),
                TernaryFilter::make('is_global')
                    ->label(__('feature_flags.is_global')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
