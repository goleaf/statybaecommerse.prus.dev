<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class CategoriesRelationManager extends BaseRelationManager
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
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
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
                RelationManagerRepeaterAction::make(),
                AttachAction::make()
                    ->preloadRecordSelect(),
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
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