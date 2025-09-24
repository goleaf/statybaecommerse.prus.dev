<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Categories';

    protected static ?string $modelLabel = 'Category';

    protected static ?string $pluralModelLabel = 'Categories';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('categories.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('categories.fields.slug'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label(__('categories.fields.description'))
                    ->maxLength(500),
                Toggle::make('is_active')
                    ->label(__('categories.fields.is_active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('categories.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('categories.fields.slug'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('description')
                    ->label(__('categories.fields.description'))
                    ->limit(50)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('categories.fields.is_active'))
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DetachAction::make(),
            ])
            ->defaultSort('name');
    }
}
