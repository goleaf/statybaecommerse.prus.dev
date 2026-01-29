<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'couponUsages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('coupon_id')
                    ->required(),
                TextInput::make('discount_amount')
                    ->numeric()
                    ->required(),
                DateTimePicker::make('used_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('discount_amount')
            ->columns([
                TextColumn::make('coupon.code')
                    ->label('Coupon Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->money()
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label('Order ID')
                    ->sortable(),
                TextColumn::make('used_at')
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
