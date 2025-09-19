<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use App\Models\Image;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('image_id')
                    ->label(__('news.image'))
                    ->relationship('image', 'filename')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\FileUpload::make('filename')
                            ->required()
                            ->image()
                            ->maxSize(5120)
                            ->directory('news-images'),
                        Forms\Components\TextInput::make('alt_text')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('caption')
                            ->maxLength(500),
                        Forms\Components\Select::make('type')
                            ->options([
                                'featured' => __('images.types.featured'),
                                'gallery' => __('images.types.gallery'),
                                'thumbnail' => __('images.types.thumbnail'),
                                'banner' => __('images.types.banner'),
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('news.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image.filename')
            ->columns([
                Tables\Columns\ImageColumn::make('image.filename')
                    ->label(__('news.image'))
                    ->size(60)
                    ->square(),

                Tables\Columns\TextColumn::make('image.alt_text')
                    ->label(__('news.alt_text'))
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('image.title')
                    ->label(__('news.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('image.caption')
                    ->label(__('news.caption'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('image.type')
                    ->label(__('news.type'))
                    ->formatStateUsing(fn (string $state): string => __("images.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'featured' => 'primary',
                        'gallery' => 'info',
                        'thumbnail' => 'success',
                        'banner' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('image.is_active')
                    ->label(__('news.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('news.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('image.created_at')
                    ->label(__('news.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('image')
                    ->label(__('news.image'))
                    ->relationship('image', 'filename')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('news.type'))
                    ->options([
                        'featured' => __('images.types.featured'),
                        'gallery' => __('images.types.gallery'),
                        'thumbnail' => __('images.types.thumbnail'),
                        'banner' => __('images.types.banner'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('news.is_active'))
                    ->boolean()
                    ->trueLabel(__('news.active_only'))
                    ->falseLabel(__('news.inactive_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
