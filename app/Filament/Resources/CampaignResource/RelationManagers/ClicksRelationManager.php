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

final class ClicksRelationManager extends RelationManager
{
    protected static string $relationship = 'clicks';

    protected static ?string $title = 'Campaign Clicks';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('click_type')
                ->label('Click Type')
                ->options([
                    'cta' => 'CTA',
                    'banner' => 'Banner',
                    'link' => 'Link',
                    'button' => 'Button',
                ])
                ->required(),
            TextInput::make('clicked_url')
                ->label('Clicked URL')
                ->url()
                ->maxLength(500),
            TextInput::make('session_id')
                ->label('Session ID')
                ->maxLength(255),
            TextInput::make('ip_address')
                ->label('IP Address')
                ->maxLength(45),
            TextInput::make('user_agent')
                ->label('User Agent')
                ->maxLength(500),
            TextInput::make('customer_id')
                ->label('Customer ID')
                ->numeric(),
            DateTimePicker::make('clicked_at')
                ->label('Clicked At')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('click_type')
                    ->label('Click Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cta' => 'success',
                        'banner' => 'info',
                        'link' => 'warning',
                        'button' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('clicked_url')
                    ->label('Clicked URL')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('session_id')
                    ->label('Session ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_id')
                    ->label('Customer ID')
                    ->sortable(),
                TextColumn::make('clicked_at')
                    ->label('Clicked At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('click_type')
                    ->label('Click Type')
                    ->options([
                        'cta' => 'CTA',
                        'banner' => 'Banner',
                        'link' => 'Link',
                        'button' => 'Button',
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
            ->defaultSort('clicked_at', 'desc');
    }
}
