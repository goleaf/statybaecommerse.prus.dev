<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
                FileUpload::make('path')
                    ->label(__('messages.image'))
                    ->image()
                    ->disk('public')
                    ->directory('variant-images')
                    ->required(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->maxLength(255),
                Toggle::make('is_primary')
                    ->label(__('messages.is_main'))
                    ->default(false),
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
                \Filament\Tables\Columns\IconColumn::make('is_primary')
                    ->label(__('messages.is_main'))
                    ->boolean(),
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
            ])
            ->bulkActions([
                //
            ]);
    }
}
