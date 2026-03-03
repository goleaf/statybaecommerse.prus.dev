<?php

declare(strict_types=1);

namespace App\Filament\Resources\News\RelationManagers;

use App\Models\NewsImage;
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

final class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label(__('admin.news_images.image'))
                    ->disk('public')
                    ->directory('news-images')
                    ->visibility('public')
                    ->image()
                    ->required(),
                TextInput::make('alt_text')
                    ->label(__('admin.news_images_table.columns.alt_text'))
                    ->maxLength(255),
                TextInput::make('caption')
                    ->label(__('admin.news_images_table.columns.caption'))
                    ->maxLength(255),
                Toggle::make('is_featured')
                    ->label(__('messages.featured'))
                    ->default(false),
                TextInput::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->label(__('admin.news_images.image'))
                    ->disk('public')
                    ->visibility('public')
                    ->square(),
                TextColumn::make('caption')
                    ->label(__('admin.news_images_table.columns.caption'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('alt_text')
                    ->label(__('admin.news_images_table.columns.alt_text'))
                    ->searchable()
                    ->limit(40),
                ToggleColumn::make('is_featured')
                    ->label(__('messages.featured'))
                    ->afterStateUpdated(static function (NewsImage $record, bool $state): void {
                        if (! $state) {
                            return;
                        }

                        NewsImage::withoutGlobalScopes()
                            ->where('news_id', $record->news_id)
                            ->whereKeyNot($record->getKey())
                            ->update(['is_featured' => false]);
                    }),
                TextColumn::make('sort_order')
                    ->label(__('admin.news_images_table.columns.sort_order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('file_size')
                    ->label(__('admin.news_images_table.columns.file_size'))
                    ->formatStateUsing(static function (?int $state): string {
                        if (! is_numeric($state) || (int) $state <= 0) {
                            return '-';
                        }

                        $bytes = (float) $state;
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $index = 0;

                        while ($bytes >= 1024 && $index < count($units) - 1) {
                            $bytes /= 1024;
                            $index++;
                        }

                        return number_format($bytes, 2) . ' ' . $units[$index];
                    })
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
