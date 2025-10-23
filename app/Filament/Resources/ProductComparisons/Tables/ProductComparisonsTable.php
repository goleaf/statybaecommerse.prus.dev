<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductComparisons\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

class ProductComparisonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('product_comparisons.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('product_comparisons.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session_id')
                    ->label(__('product_comparisons.session_id'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('product_comparisons.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('product_comparisons.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('product_comparisons.user'))
                    ->relationship('user', 'name'),
                SelectFilter::make('product_id')
                    ->label(__('product_comparisons.product'))
                    ->relationship('product', 'name'),
                Filter::make('created_at')
                    ->form([
                        Flatpickr::make('created_from')
                            ->time(false)
                            ->format('Y-m-d')
                            ->rangePicker()
                            ->label(__('product_comparisons.created_from')),
                        Flatpickr::make('created_until')
                            ->time(false)
                            ->format('Y-m-d')
                            ->rangePicker()
                            ->label(__('product_comparisons.created_until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
