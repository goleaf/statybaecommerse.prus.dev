<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->withoutGlobalScopes();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('path')
                ->label(__('messages.image'))
                ->image()
                ->disk('public')
                ->directory('products'),
            TextInput::make('alt_text')
                ->label(__('admin.products.alt_text'))
                ->maxLength(255),
            TextInput::make('sort_order')
                ->label(__('admin.products.sort_order'))
                ->numeric()
                ->integer()
                ->default(0),
            Toggle::make('is_active')
                ->label(__('admin.products.is_active'))
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                ImageColumn::make('path')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->square(),
                TextColumn::make('alt_text')
                    ->label(__('admin.products.alt_text'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('admin.products.sort_order'))
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.products.is_active')),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
