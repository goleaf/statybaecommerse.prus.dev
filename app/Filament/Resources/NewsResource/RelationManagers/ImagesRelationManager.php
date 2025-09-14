<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final /**
 * ImagesRelationManager
 * 
 * Filament resource for admin panel management.
 */
class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $form): Schema
    {
        return $schema->components([
            Forms\Components\FileUpload::make('file_path')
                ->label(__('admin.news.images.fields.file_path'))
                ->required()
                ->image()
                ->directory('news-images')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                ->maxSize(5120), // 5MB
            Forms\Components\TextInput::make('alt_text')
                ->label(__('admin.news.images.fields.alt_text'))
                ->maxLength(255),
            Forms\Components\Textarea::make('caption')
                ->label(__('admin.news.images.fields.caption'))
                ->maxLength(500)
                ->rows(3),
            Forms\Components\Toggle::make('is_featured')
                ->label(__('admin.news.images.fields.is_featured')),
            Forms\Components\TextInput::make('sort_order')
                ->label(__('admin.news.images.fields.sort_order'))
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_path')
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label(__('admin.news.images.fields.file_path'))
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label(__('admin.news.images.fields.alt_text'))
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('caption')
                    ->label(__('admin.news.images.fields.caption'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.news.images.fields.is_featured'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.news.images.fields.sort_order'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label(__('admin.news.images.fields.file_size'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('file_size', $direction);
                    }),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label(__('admin.news.images.fields.mime_type'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.news.images.filters.is_featured')),
                Tables\Filters\SelectFilter::make('file_type')
                    ->label(__('admin.news.images.filters.file_type'))
                    ->options([
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/gif' => 'GIF',
                        'image/webp' => 'WebP',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
