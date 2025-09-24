<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ViewsRelationManager extends RelationManager
{
    protected static string $relationship = 'views';

    protected static ?string $title = 'Campaign Views';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('session_id')
                ->label('Session ID')
                ->maxLength(255),
            TextInput::make('ip_address')
                ->label('IP Address')
                ->maxLength(45),
            TextInput::make('user_agent')
                ->label('User Agent')
                ->maxLength(500),
            TextInput::make('referer')
                ->label('Referer')
                ->maxLength(500),
            TextInput::make('customer_id')
                ->label('Customer ID')
                ->numeric(),
            DateTimePicker::make('viewed_at')
                ->label('Viewed At')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session_id')
                    ->label('Session ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('referer')
                    ->label('Referer')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('customer_id')
                    ->label('Customer ID')
                    ->sortable(),
                TextColumn::make('viewed_at')
                    ->label('Viewed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
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
            ->defaultSort('viewed_at', 'desc');
    }
}
