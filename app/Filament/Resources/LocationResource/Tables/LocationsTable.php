<?php

declare(strict_types=1);

namespace App\Filament\Resources\LocationResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('address_line_1')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('address_line_2')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('city')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('state')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country_code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country.name')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->label(__('admin.labels.email_address'))
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->sortable()
                    ->boolean(),
                IconColumn::make('is_default')
                    ->sortable()
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
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('messages.type'))
                    ->options([
                        'warehouse'    => Str::headline('warehouse'),
                        'store'        => Str::headline('store'),
                        'office'       => Str::headline('office'),
                        'pickup_point' => Str::headline('pickup_point'),
                        'other'        => Str::headline('other'),
                    ]),
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
