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

final class ProductTargetsRelationManager extends RelationManager
{
    protected static string $relationship = 'productTargets';

    protected static ?string $title = 'Product Targets';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('product_id')
                ->label('Product')
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('target_type')
                ->label('Target Type')
                ->maxLength(255),
            TextInput::make('priority')
                ->label('Priority')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label('Target Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Priority')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name'),
                SelectFilter::make('target_type')
                    ->label('Target Type')
                    ->options([
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                        'excluded' => 'Excluded',
                    ]),
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
            ->defaultSort('priority', 'desc');
    }
}
