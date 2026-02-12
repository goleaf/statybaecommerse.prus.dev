<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Price;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $recordTitleAttribute = 'amount';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_id')
                    ->label(__('messages.currency'))
                    ->relationship('currency', 'code')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('amount')
                    ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                    ->required()
                    ->numeric()
                    ->step(0.0001),
                Select::make('type')
                    ->label(__('messages.Type'))
                    ->options([
                        'retail' => 'retail',
                        'wholesale' => 'wholesale',
                        'special' => 'special',
                        'sale' => 'sale',
                    ])
                    ->default('retail')
                    ->required(),
                DateTimePicker::make('starts_at')
                    ->label(__('admin.prices.valid_from')),
                DateTimePicker::make('ends_at')
                    ->label(__('admin.prices.valid_until')),
                Toggle::make('is_enabled')
                    ->label(__('messages.Enabled'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('currency.code')
                    ->label(__('messages.currency'))
                    ->badge(),
                TextColumn::make('amount')
                    ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                    ->money(fn (Price $record) => $record->currency?->code ?? 'EUR'),
                TextColumn::make('type')
                    ->label(__('messages.Type'))
                    ->badge(),
                IconColumn::make('is_enabled')
                    ->label(__('messages.Enabled'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label(__('admin.prices.valid_from'))
                    ->dateTime(),
                TextColumn::make('ends_at')
                    ->label(__('admin.prices.valid_until'))
                    ->dateTime()
                    ->placeholder(__('admin.prices.no_expiry')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
