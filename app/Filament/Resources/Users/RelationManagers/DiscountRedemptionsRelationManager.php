<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscountRedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'discountRedemptions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('discount_id')
                    ->relationship('discount', 'name')
                    ->required(),
                Select::make('order_id')
                    ->relationship('order', 'number')
                    ->required(),
                TextInput::make('amount_saved')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                DateTimePicker::make('redeemed_at')
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount_saved')
            ->columns([
                TextColumn::make('discount.name')
                    ->label('Discount')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount_saved')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('redeemed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
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
