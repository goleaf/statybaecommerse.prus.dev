<?php

declare(strict_types=1);

namespace App\Filament\Resources\WarehouseResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class WarehousesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('country_code')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                IconColumn::make('is_enabled')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('has_coordinates')
                    ->label(__('messages.coordinates'))
                    ->options([
                        'yes' => 'Yes',
                        'no'  => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === 'yes') {
                            return $query
                                ->whereNotNull('latitude')
                                ->whereNotNull('longitude');
                        }

                        if ($value === 'no') {
                            return $query->where(static function (Builder $builder): void {
                                $builder->whereNull('latitude')->orWhereNull('longitude');
                            });
                        }

                        return $query;
                    }),
                SelectFilter::make('has_opening_hours')
                    ->label(__('messages.hours'))
                    ->options([
                        'yes' => 'Yes',
                        'no'  => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === 'yes') {
                            return $query->whereNotNull('opening_hours');
                        }

                        if ($value === 'no') {
                            return $query->whereNull('opening_hours');
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
