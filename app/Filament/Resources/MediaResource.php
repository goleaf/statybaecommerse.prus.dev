<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use BackedEnum;
use UnitEnum;

final class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::Content;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.media');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.media');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.media_files');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Media Information'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('file_name')
                            ->label(__('File Name'))
                            ->disabled(),
                        Forms\Components\TextInput::make('mime_type')
                            ->label(__('MIME Type'))
                            ->disabled(),
                        Forms\Components\TextInput::make('size')
                            ->label(__('File Size'))
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? number_format($state / 1024, 2) . ' KB' : ''),
                        Forms\Components\Select::make('collection_name')
                            ->label(__('Collection'))
                            ->options([
                                'product-images' => __('Product Images'),
                                'product-gallery' => __('Product Gallery'),
                                'brand-logos' => __('Brand Logos'),
                                'category-images' => __('Category Images'),
                                'user-avatars' => __('User Avatars'),
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('custom_properties.alt')
                            ->label(__('Alt Text'))
                            ->helperText(__('Alternative text for accessibility')),
                        Forms\Components\Textarea::make('custom_properties.caption')
                            ->label(__('Caption'))
                            ->helperText(__('Image caption for display')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label(__('Preview'))
                    ->size(60)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('file_name')
                    ->label(__('File Name'))
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('collection_name')
                    ->label(__('Collection'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'product-images' => 'success',
                        'product-gallery' => 'info',
                        'brand-logos' => 'warning',
                        'category-images' => 'primary',
                        'user-avatars' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label(__('Type'))
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('size')
                    ->label(__('Size'))
                    ->formatStateUsing(fn($state) => $state ? number_format($state / 1024, 2) . ' KB' : '')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('model_type')
                    ->label(__('Used By'))
                    ->formatStateUsing(fn(string $state): string => class_basename($state))
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Uploaded'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection_name')
                    ->label(__('Collection'))
                    ->options([
                        'product-images' => __('Product Images'),
                        'product-gallery' => __('Product Gallery'),
                        'brand-logos' => __('Brand Logos'),
                        'category-images' => __('Category Images'),
                        'user-avatars' => __('User Avatars'),
                    ]),
                Tables\Filters\SelectFilter::make('mime_type')
                    ->label(__('File Type'))
                    ->options([
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/webp' => 'WebP',
                        'image/gif' => 'GIF',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('download')
                    ->label(__('Download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Media $record): string => $record->getUrl())
                    ->openUrlInNewTab(),
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'view' => Pages\ViewMedia::route('/{record}'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'file_name', 'collection_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Collection' => $record->collection_name,
            'Type' => $record->mime_type,
            'Size' => number_format($record->size / 1024, 2) . ' KB',
            'Used By' => $record->model_type ? class_basename($record->model_type) : __('Unused'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;  // Media is uploaded through other resources
    }
}
