<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Filament\Resources\NewsResource\RelationManagers;
use App\Models\News;
use UnitEnum;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

final class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Content Management';

    public static function getModelLabel(): string
    {
        return __('admin.models.news');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.news_list');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.news.title');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('admin.news.fields.title'))
                    ->schema([
                        Forms\Components\Tabs::make('news_tabs')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make(__('admin.news.fields.title'))
                                    ->schema([
                                        Forms\Components\TextInput::make('translations.title')
                                            ->label(__('admin.news.fields.title'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                                if ($get('translations.slug') === '') {
                                                    $set('translations.slug', \Str::slug($state));
                                                }
                                            }),
                                        Forms\Components\TextInput::make('translations.slug')
                                            ->label(__('admin.news.fields.slug'))
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->rules(['regex:/^[a-z0-9-]+$/']),
                                        Forms\Components\Textarea::make('translations.summary')
                                            ->label(__('admin.news.fields.summary'))
                                            ->maxLength(500)
                                            ->rows(3),
                                        Forms\Components\RichEditor::make('translations.content')
                                            ->label(__('admin.news.fields.content'))
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Forms\Components\Tabs\Tab::make('SEO')
                                    ->schema([
                                        Forms\Components\TextInput::make('translations.seo_title')
                                            ->label(__('admin.news.fields.seo_title'))
                                            ->maxLength(60)
                                            ->helperText(__('admin.seo_data.help.title')),
                                        Forms\Components\Textarea::make('translations.seo_description')
                                            ->label(__('admin.news.fields.seo_description'))
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText(__('admin.seo_data.help.description')),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make(__('admin.news.fields.content'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label(__('admin.news.fields.is_visible'))
                                    ->default(true),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label(__('admin.news.fields.is_featured')),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label(__('admin.news.fields.published_at'))
                                    ->default(now()),
                                Forms\Components\TextInput::make('author_name')
                                    ->label(__('admin.news.fields.author_name'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('author_email')
                                    ->label(__('admin.news.fields.author_email'))
                                    ->email()
                                    ->maxLength(255),
                            ]),
                    ]),

                Forms\Components\Section::make(__('admin.news.fields.categories'))
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->label(__('admin.news.fields.categories'))
                            ->multiple()
                            ->relationship('categories', 'translations.name')
                            ->preload()
                            ->searchable(),
                    ]),

                Forms\Components\Section::make(__('admin.news.fields.tags'))
                    ->schema([
                        Forms\Components\Select::make('tags')
                            ->label(__('admin.news.fields.tags'))
                            ->multiple()
                            ->relationship('tags', 'translations.name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\ColorPicker::make('color'),
                            ]),
                    ]),

                Forms\Components\Section::make(__('admin.news.fields.meta_data'))
                    ->schema([
                        Forms\Components\KeyValue::make('meta_data')
                            ->label(__('admin.news.fields.meta_data'))
                            ->keyLabel(__('admin.news.fields.meta_key'))
                            ->valueLabel(__('admin.news.fields.meta_value')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label(__('admin.news.fields.images'))
                    ->getStateUsing(function (News $record) {
                        $featuredImage = $record->images()->where('is_featured', true)->first();
                        return $featuredImage ? $featuredImage->url : null;
                    })
                    ->circular()
                    ->defaultImageUrl('/images/placeholder-news.jpg'),
                Tables\Columns\TextColumn::make('translations.title')
                    ->label(__('admin.news.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('translations.summary')
                    ->label(__('admin.news.fields.summary'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('admin.news.fields.is_visible'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.news.fields.is_featured'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('admin.news.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author_name')
                    ->label(__('admin.news.fields.author_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label(__('admin.news.fields.view_count'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories_count')
                    ->label(__('admin.news.fields.categories'))
                    ->counts('categories'),
                Tables\Columns\TextColumn::make('tags_count')
                    ->label(__('admin.news.fields.tags'))
                    ->counts('tags'),
                Tables\Columns\TextColumn::make('comments_count')
                    ->label(__('admin.news.fields.comments'))
                    ->counts('comments'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.news.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.news.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('admin.news.filters.is_visible')),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.news.filters.is_featured')),
                Tables\Filters\Filter::make('published')
                    ->label(__('admin.news.filters.published'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at')
                        ->where('published_at', '<=', now())),
                Tables\Filters\Filter::make('unpublished')
                    ->label(__('admin.news.filters.unpublished'))
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '>', now());
                    })),
                Tables\Filters\SelectFilter::make('categories')
                    ->label(__('admin.news.fields.categories'))
                    ->relationship('categories', 'translations.name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('tags')
                    ->label(__('admin.news.fields.tags'))
                    ->relationship('tags', 'translations.name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_images')
                    ->label(__('admin.news.filters.has_images'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('images')),
                Tables\Filters\Filter::make('has_comments')
                    ->label(__('admin.news.filters.has_comments'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('comments')),
                Tables\Filters\Filter::make('author')
                    ->label(__('admin.news.filters.author'))
                    ->form([
                        Forms\Components\TextInput::make('author_name')
                            ->label(__('admin.news.fields.author_name')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['author_name'],
                            fn (Builder $query, $authorName): Builder => $query->where('author_name', 'like', "%{$authorName}%"),
                        );
                    }),
                Tables\Filters\Filter::make('published_from')
                    ->label(__('admin.news.filters.published_from'))
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label(__('admin.news.fields.published_at')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['published_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                        );
                    }),
                Tables\Filters\Filter::make('published_until')
                    ->label(__('admin.news.filters.published_until'))
                    ->form([
                        Forms\Components\DatePicker::make('published_until')
                            ->label(__('admin.news.fields.published_at')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['published_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('publish')
                    ->label(__('admin.news.actions.publish'))
                    ->icon('heroicon-o-check')
                    ->action(function (News $record) {
                        $record->update([
                            'is_visible' => true,
                            'published_at' => now(),
                        ]);
                    })
                    ->visible(fn (News $record): bool => !$record->isPublished()),
                Tables\Actions\Action::make('unpublish')
                    ->label(__('admin.news.actions.unpublish'))
                    ->icon('heroicon-o-x-mark')
                    ->action(function (News $record) {
                        $record->update([
                            'is_visible' => false,
                            'published_at' => null,
                        ]);
                    })
                    ->visible(fn (News $record): bool => $record->isPublished()),
                Tables\Actions\Action::make('feature')
                    ->label(__('admin.news.actions.feature'))
                    ->icon('heroicon-o-star')
                    ->action(function (News $record) {
                        $record->update(['is_featured' => true]);
                    })
                    ->visible(fn (News $record): bool => !$record->is_featured),
                Tables\Actions\Action::make('unfeature')
                    ->label(__('admin.news.actions.unfeature'))
                    ->icon('heroicon-o-star')
                    ->action(function (News $record) {
                        $record->update(['is_featured' => false]);
                    })
                    ->visible(fn (News $record): bool => $record->is_featured),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label(__('admin.news.actions.publish'))
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update([
                                'is_visible' => true,
                                'published_at' => now(),
                            ]);
                        }),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label(__('admin.news.actions.unpublish'))
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update([
                                'is_visible' => false,
                                'published_at' => null,
                            ]);
                        }),
                    Tables\Actions\BulkAction::make('feature')
                        ->label(__('admin.news.actions.feature'))
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each->update(['is_featured' => true]);
                        }),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label(__('admin.news.actions.unfeature'))
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each->update(['is_featured' => false]);
                        }),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CategoriesRelationManager::class,
            RelationManagers\TagsRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'view' => Pages\ViewNews::route('/{record}'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}