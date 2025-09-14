<?php

declare(strict_types=1);

namespace App\Filament\Resources\ZoneResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final /**
 * DiscountsRelationManager
 * 
 * Filament resource for admin panel management.
 */
class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    protected static ?string $title = 'zones.discounts';

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('discounts.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label(__('discounts.code'))
                    ->maxLength(50),
                Forms\Components\Select::make('type')
                    ->label(__('discounts.type'))
                    ->options([
                        'percentage' => __('discounts.percentage'),
                        'fixed' => __('discounts.fixed'),
                        'free_shipping' => __('discounts.free_shipping'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label(__('discounts.value'))
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('discounts.is_active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('discounts.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('discounts.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('discounts.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        'free_shipping' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('discounts.value'))
                    ->formatStateUsing(fn (string $state, $record): string => $record->type === 'percentage' ? $state.'%' : '€'.$state
                    ),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('discounts.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('discounts.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('discounts.type'))
                    ->options([
                        'percentage' => __('discounts.percentage'),
                        'fixed' => __('discounts.fixed'),
                        'free_shipping' => __('discounts.free_shipping'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('discounts.is_active')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('zones.create_discount')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
