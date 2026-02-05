<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
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
                FileUpload::make('path')
                    ->label(__('messages.image'))
                    ->image()
                    ->disk('public')
                    ->directory('product-images')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->imageEditor()
                    ->imagePreviewHeight('250'),
                TextInput::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->numeric()
                    ->default(0),
                Toggle::make('is_default')
                    ->label(__('messages.is_main'))
                    ->default(fn (RelationManager $livewire): bool => $livewire->getOwnerRecord()
                        ->images()
                        ->withoutGlobalScopes()
                        ->count() === 0),
                Toggle::make('is_active')
                    ->label(__('messages.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn ($query) => $query->withoutGlobalScopes())
            ->columns([
                ImageColumn::make('url')
                    ->label(__('messages.preview')),
                TextColumn::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('messages.is_main'))
                    ->boolean()
                    ->trueIcon('heroicon-m-star')
                    ->falseIcon('heroicon-m-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
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
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
