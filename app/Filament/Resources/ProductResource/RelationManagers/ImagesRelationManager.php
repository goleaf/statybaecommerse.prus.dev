<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Product Images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\FileUpload::make('image')
                    ->label(__('products.images.image'))
                    ->image()
                    ->required()
                    ->maxSize(10240)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->directory('products/images')
                    ->visibility('public'),
                Forms\Components\TextInput::make('alt_text')
                    ->label(__('products.images.alt_text'))
                    ->maxLength(255)
                    ->helperText(__('products.images.alt_text_help')),
                Forms\Components\TextInput::make('title')
                    ->label(__('products.images.title'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('products.images.description'))
                    ->maxLength(500)
                    ->rows(3),
                Forms\Components\Select::make('type')
                    ->label(__('products.images.type'))
                    ->options([
                        'main' => __('products.images.types.main'),
                        'gallery' => __('products.images.types.gallery'),
                        'thumbnail' => __('products.images.types.thumbnail'),
                        'banner' => __('products.images.types.banner'),
                        'icon' => __('products.images.types.icon'),
                    ])
                    ->required()
                    ->default('gallery'),
                Forms\Components\Toggle::make('is_primary')
                    ->label(__('products.images.is_primary'))
                    ->helperText(__('products.images.is_primary_help')),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('products.images.is_active'))
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('products.images.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('products.images.image'))
                    ->size(60)
                    ->square(),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label(__('products.images.alt_text'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('products.images.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('products.images.type'))
                    ->formatStateUsing(fn (string $state): string => __("products.images.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'main' => 'primary',
                        'gallery' => 'success',
                        'thumbnail' => 'warning',
                        'banner' => 'info',
                        'icon' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label(__('products.images.is_primary'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('products.images.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('products.images.sort_order'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('products.images.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('products.images.type'))
                    ->options([
                        'main' => __('products.images.types.main'),
                        'gallery' => __('products.images.types.gallery'),
                        'thumbnail' => __('products.images.types.thumbnail'),
                        'banner' => __('products.images.types.banner'),
                        'icon' => __('products.images.types.icon'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label(__('products.images.is_primary'))
                    ->boolean()
                    ->trueLabel(__('products.images.primary_only'))
                    ->falseLabel(__('products.images.non_primary_only'))
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('products.images.is_active'))
                    ->boolean()
                    ->trueLabel(__('products.images.active_only'))
                    ->falseLabel(__('products.images.inactive_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('sort_order');
    }
}
