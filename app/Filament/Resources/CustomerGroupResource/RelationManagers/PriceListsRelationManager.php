<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class PriceListsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'priceLists';

    protected static ?string $title = 'customer_groups.relation_price_lists';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('currency')
                    ->maxLength(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('price_lists.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('price_lists.currency'))
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('price_lists.is_default')),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\AttachAction::make()
                    ->label(__('customer_groups.attach_price_list')),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label(__('customer_groups.detach_price_list')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('customer_groups.detach_selected')),
                ]),
            ]);
    }
}
