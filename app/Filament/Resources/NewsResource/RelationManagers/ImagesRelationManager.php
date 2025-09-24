<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

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

    protected static ?string $title = 'Images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\FileUpload::make('file_path')
                    ->label(__('news.fields.file_path'))
                    ->required()
                    ->image()
                    ->maxSize(10240)
                    ->directory('news-images')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('alt_text')
                    ->label(__('news.fields.alt_text'))
                    ->maxLength(255)
                    ->columnSpan(1),
                Forms\Components\TextInput::make('caption')
                    ->label(__('news.fields.caption'))
                    ->maxLength(500)
                    ->columnSpan(1),
                Forms\Components\Toggle::make('is_featured')
                    ->label(__('news.fields.is_featured'))
                    ->default(false)
                    ->columnSpan(1),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('news.fields.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->columnSpan(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label(__('news.fields.image'))
                    ->size(60)
                    ->square()
                    ->disk('public'),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label(__('news.fields.alt_text'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('caption')
                    ->label(__('news.fields.caption'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('news.fields.is_featured'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('news.fields.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_size')
                    ->label(__('news.fields.file_size'))
                    ->formatStateUsing(fn ($state) => $this->formatFileSize($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label(__('news.fields.mime_type'))
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('news.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('news.fields.is_featured'))
                    ->boolean()
                    ->trueLabel(__('news.featured_only'))
                    ->falseLabel(__('news.non_featured_only'))
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

        return $table;
    }

    private function formatFileSize(?int $bytes): string
    {
        if (! $bytes) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
