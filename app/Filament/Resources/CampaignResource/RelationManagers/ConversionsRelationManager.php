<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ConversionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversions';

    protected static ?string $title = 'Campaign Conversions';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('conversion_type')
                ->label('Conversion Type')
                ->options([
                    'purchase' => 'Purchase',
                    'signup' => 'Signup',
                    'download' => 'Download',
                    'contact' => 'Contact',
                ])
                ->required(),
            TextInput::make('conversion_value')
                ->label('Value')
                ->numeric(),
            TextInput::make('currency')
                ->label('Currency')
                ->default('EUR')
                ->maxLength(3),
            TextInput::make('customer_id')
                ->label('Customer ID')
                ->numeric(),
            DateTimePicker::make('converted_at')
                ->label('Converted At')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('conversion_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'signup' => 'info',
                        'download' => 'warning',
                        'contact' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('conversion_value')
                    ->label('Value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Currency')
                    ->sortable(),
                TextColumn::make('customer_id')
                    ->label('Customer ID')
                    ->sortable(),
                TextColumn::make('converted_at')
                    ->label('Converted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('conversion_type')
                    ->label('Type')
                    ->options([
                        'purchase' => 'Purchase',
                        'signup' => 'Signup',
                        'download' => 'Download',
                        'contact' => 'Contact',
                    ]),
                SelectFilter::make('customer_id')
                    ->label('Has Customer')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === '1') {
                            return $query->whereNotNull('customer_id');
                        } elseif ($data['value'] === '0') {
                            return $query->whereNull('customer_id');
                        }

                        return $query;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('converted_at', 'desc');
    }
}
