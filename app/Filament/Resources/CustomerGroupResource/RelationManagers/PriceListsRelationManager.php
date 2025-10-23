<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class PriceListsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'priceLists';

    protected static ?string $title = 'customer_groups.relation_price_lists';

    public function form(Schema $form): Schema
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
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
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