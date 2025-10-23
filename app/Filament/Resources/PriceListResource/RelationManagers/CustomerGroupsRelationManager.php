<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class CustomerGroupsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'customerGroups';

    protected static ?string $title = 'Customer Groups';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('price_lists.customer_group'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('customer_groups.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label(__('price_lists.discount_percentage'))
                    ->formatStateUsing(fn (?float $state): string => $state !== null ? number_format($state, 2) . '%' : '-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('customer_groups.is_enabled'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('customer_groups.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->trueLabel(__('price_lists.active_only'))
                    ->falseLabel(__('price_lists.inactive_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'slug']),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
