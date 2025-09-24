<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsImageResource\Pages;
use App\Models\News;
use App\Models\NewsImage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

final class NewsImageResource extends Resource
{
    protected static ?string $model = NewsImage::class;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Content';
    }

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-photo';
    }

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('admin.news_images.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.news_images.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.news_images.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('admin.news_images.tabs'))
                    ->tabs([
                        Tab::make(__('admin.news_images.basic_information'))
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make(__('admin.news_images.basic_information'))
                                    ->description(__('admin.news_images.basic_information_description'))
                                    ->schema([
                                        Select::make('news_id')
                                            ->label(__('admin.news_images.news'))
                                            ->options(News::pluck('title', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $news = News::find($state);
                                                    if ($news) {
                                                        $set('sort_order', $news->images()->max('sort_order') + 1);
                                                    }
                                                }
                                            }),
                                        FileUpload::make('file_path')
                                            ->label(__('admin.news_images.file_path'))
                                            ->required()
                                            ->image()
                                            ->directory('news-images')
                                            ->visibility('public')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                            ->maxSize(5120)  // 5MB
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    $file = storage_path('app/public/' . $state);
                                                    if (file_exists($file)) {
                                                        $set('file_size', filesize($file));
                                                        $set('mime_type', mime_content_type($file));
                                                        $imageInfo = getimagesize($file);
                                                        if ($imageInfo) {
                                                            $set('dimensions', [
                                                                'width' => $imageInfo[0],
                                                                'height' => $imageInfo[1],
                                                            ]);
                                                        }
                                                    }
                                                }
                                            }),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('alt_text')
                                                    ->label(__('admin.news_images.alt_text'))
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.news_images.alt_text_help')),
                                                TextInput::make('sort_order')
                                                    ->label(__('admin.news_images.sort_order'))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->helperText(__('admin.news_images.sort_order_help')),
                                            ]),
                                        Textarea::make('caption')
                                            ->label(__('admin.news_images.caption'))
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->maxLength(500)
                                            ->helperText(__('admin.news_images.caption_help')),
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('is_featured')
                                                    ->label(__('admin.news_images.is_featured'))
                                                    ->default(false)
                                                    ->helperText(__('admin.news_images.is_featured_help')),
                                                TextInput::make('file_size')
                                                    ->label(__('admin.news_images.file_size'))
                                                    ->numeric()
                                                    ->disabled()
                                                    ->formatStateUsing(fn($state) => $state ? number_format($state / 1024, 2) . ' KB' : ''),
                                                TextInput::make('mime_type')
                                                    ->label(__('admin.news_images.mime_type'))
                                                    ->disabled(),
                                            ]),
                                    ]),
                            ]),
                        Tab::make(__('admin.news_images.technical_details'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make(__('admin.news_images.technical_details'))
                                    ->description(__('admin.news_images.technical_details_description'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('dimensions.width')
                                                    ->label(__('admin.news_images.width'))
                                                    ->numeric()
                                                    ->disabled()
                                                    ->suffix('px'),
                                                TextInput::make('dimensions.height')
                                                    ->label(__('admin.news_images.height'))
                                                    ->numeric()
                                                    ->disabled()
                                                    ->suffix('px'),
                                            ]),
                                        Placeholder::make('file_info')
                                            ->label(__('admin.news_images.file_info'))
                                            ->content(function (callable $get) {
                                                $fileSize = $get('file_size');
                                                $mimeType = $get('mime_type');
                                                $dimensions = $get('dimensions');

                                                $info = [];
                                                if ($fileSize) {
                                                    $info[] = __('admin.news_images.file_size') . ': ' . number_format($fileSize / 1024, 2) . ' KB';
                                                }
                                                if ($mimeType) {
                                                    $info[] = __('admin.news_images.mime_type') . ': ' . $mimeType;
                                                }
                                                if ($dimensions && isset($dimensions['width']) && isset($dimensions['height'])) {
                                                    $info[] = __('admin.news_images.dimensions') . ': ' . $dimensions['width'] . 'x' . $dimensions['height'];
                                                }

                                                return implode(' | ', $info);
                                            }),
                                    ]),
                            ]),
                        Tab::make(__('admin.news_images.seo_metadata'))
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make(__('admin.news_images.seo_metadata'))
                                    ->description(__('admin.news_images.seo_metadata_description'))
                                    ->schema([
                                        TextInput::make('alt_text')
                                            ->label(__('admin.news_images.alt_text'))
                                            ->maxLength(255)
                                            ->helperText(__('admin.news_images.alt_text_seo_help')),
                                        Textarea::make('caption')
                                            ->label(__('admin.news_images.caption'))
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText(__('admin.news_images.caption_seo_help')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->label(__('admin.news_images.image'))
                    ->square()
                    ->size(80)
                    ->defaultImageUrl(url('/images/placeholder-image.png'))
                    ->extraImgAttributes(['class' => 'rounded-lg shadow-sm']),
                TextColumn::make('news.title')
                    ->label(__('admin.news_images.news'))
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 40 ? $state : null;
                    })
                    ->url(fn($record) => route('admin.news.edit', $record->news_id))
                    ->color('primary'),
                TextColumn::make('alt_text')
                    ->label(__('admin.news_images.alt_text'))
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->placeholder(__('admin.news_images.no_alt_text')),
                TextColumn::make('caption')
                    ->label(__('admin.news_images.caption'))
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 40 ? $state : null;
                    })
                    ->placeholder(__('admin.news_images.no_caption')),
                BadgeColumn::make('is_featured')
                    ->label(__('admin.news_images.is_featured'))
                    ->formatStateUsing(fn($state) => $state ? __('admin.common.yes') : __('admin.common.no'))
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ])
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('admin.news_images.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('file_size')
                    ->label(__('admin.news_images.file_size'))
                    ->formatStateUsing(fn($state) => $state ? number_format($state / 1024, 2) . ' KB' : '')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('secondary'),
                BadgeColumn::make('mime_type')
                    ->label(__('admin.news_images.mime_type'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/gif' => 'GIF',
                        'image/webp' => 'WebP',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'image/jpeg',
                        'info' => 'image/png',
                        'warning' => 'image/gif',
                        'primary' => 'image/webp',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dimensions')
                    ->label(__('admin.news_images.dimensions'))
                    ->formatStateUsing(fn($state) => $state && isset($state['width'], $state['height'])
                        ? $state['width'] . 'x' . $state['height']
                        : '')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('news_id')
                    ->label(__('admin.news_images.news'))
                    ->options(News::pluck('title', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('is_featured')
                    ->label(__('admin.news_images.is_featured'))
                    ->boolean()
                    ->placeholder(__('admin.common.all')),
                SelectFilter::make('mime_type')
                    ->label(__('admin.news_images.mime_type'))
                    ->options([
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/gif' => 'GIF',
                        'image/webp' => 'WebP',
                    ])
                    ->multiple(),
                Filter::make('large_files')
                    ->label(__('admin.news_images.large_files'))
                    ->query(fn(Builder $query): Builder => $query->where('file_size', '>', 1024 * 1024))  // > 1MB
                    ->toggle(),
                Filter::make('recent_uploads')
                    ->label(__('admin.news_images.recent_uploads'))
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->toggle(),
                Filter::make('no_alt_text')
                    ->label(__('admin.news_images.no_alt_text'))
                    ->query(fn(Builder $query): Builder => $query->whereNull('alt_text')->orWhere('alt_text', ''))
                    ->toggle(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label(__('admin.common.view'))
                        ->icon('heroicon-o-eye'),
                    EditAction::make()
                        ->label(__('admin.common.edit'))
                        ->icon('heroicon-o-pencil'),
                    Action::make('duplicate')
                        ->label(__('admin.news_images.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (NewsImage $record) {
                            $newRecord = $record->replicate();
                            $newRecord->sort_order = $record->news->images()->max('sort_order') + 1;
                            $newRecord->save();
                        })
                        ->requiresConfirmation(),
                    Action::make('download')
                        ->label(__('admin.news_images.download'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn(NewsImage $record) => asset('storage/' . $record->file_path))
                        ->openUrlInNewTab(),
                    DeleteAction::make()
                        ->label(__('admin.common.delete'))
                        ->icon('heroicon-o-trash'),
                ])
                    ->label(__('admin.common.actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('admin.common.delete_selected')),
                    BulkAction::make('set_featured')
                        ->label(__('admin.news_images.set_featured'))
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => true]);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unset_featured')
                        ->label(__('admin.news_images.unset_featured'))
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => false]);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('reorder')
                        ->label(__('admin.news_images.reorder'))
                        ->icon('heroicon-o-arrows-up-down')
                        ->action(function (Collection $records) {
                            $records->each(function ($record, $index) {
                                $record->update(['sort_order' => $index + 1]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsImages::route('/'),
            'create' => Pages\CreateNewsImage::route('/create'),
            'view' => Pages\ViewNewsImage::route('/{record}'),
            'edit' => Pages\EditNewsImage::route('/{record}/edit'),
        ];
    }
}
