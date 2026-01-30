<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $recordTitleAttribute = 'alt_text';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.images');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('path')
                    ->label(__('messages.path'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label(__('messages.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('url')
                    ->label(__('messages.preview')),
                TextColumn::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('messages.active')),
            ])
            ->filters([
                //
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
