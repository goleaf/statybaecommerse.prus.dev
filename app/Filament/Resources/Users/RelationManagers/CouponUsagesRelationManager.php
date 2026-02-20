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

class CouponUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'couponUsages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('coupon_id')
                    ->relationship('coupon', 'code')
                    ->required(),
                Select::make('order_id')
                    ->relationship('order', 'number')
                    ->required(),
                TextInput::make('discount_amount')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                DateTimePicker::make('used_at')
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('discount_amount')
            ->columns([
                TextColumn::make('coupon.code')
                    ->label(__('admin.labels.coupon'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('admin.labels.order'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->money('EUR')
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
