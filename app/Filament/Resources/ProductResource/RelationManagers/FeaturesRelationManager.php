<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductFeature;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'features';

    protected static ?string $recordTitleAttribute = 'feature_key';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('feature_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('feature_key')
                    ->required()
                    ->maxLength(255),
                TextInput::make('feature_value')
                    ->required()
                    ->maxLength(255),
                TextInput::make('weight')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('feature_type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('feature_key')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('feature_value')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('weight')
                    ->sortable(),
                ToggleColumn::make('is_active'),
            ])
            ->filters([
                SelectFilter::make('feature_type')
                    ->options(fn () => ProductFeature::distinct()->pluck('feature_type', 'feature_type')->toArray()),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
