<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class CustomerSegmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerSegments';

    protected static ?string $title = 'Customer Segments';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('customer_group_id')
                ->label('Customer Group')
                ->relationship('customerGroup', 'name')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('segment_name')
                ->label('Segment Name')
                ->maxLength(255),
            TextInput::make('criteria')
                ->label('Criteria')
                ->maxLength(500),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customerGroup.name')
                    ->label('Customer Group')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('segment_name')
                    ->label('Segment Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criteria')
                    ->label('Criteria')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer_group_id')
                    ->label('Customer Group')
                    ->relationship('customerGroup', 'name'),
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
            ->defaultSort('created_at', 'desc');
    }
}
