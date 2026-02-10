<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $recordTitleAttribute = 'reason';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.movements');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->required(),
                Select::make('type')
                    ->label(__('messages.type'))
                    ->options([
                        'in' => 'In',
                        'out' => 'Out',
                    ])
                    ->required(),
                TextInput::make('reason')
                    ->label(__('messages.reason'))
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->sortable(),
                TextColumn::make('reason')
                    ->label(__('messages.reason'))
                    ->searchable(),
                TextColumn::make('moved_at')
                    ->label(__('admin.inventory.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Usually movements are recorded via adjustments, but we can add create if needed
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
