<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Filament\Resources\NewsResource\RelationManagers;
use App\Models\News;
use Filament\Schemas\Schema;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Infolists;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'News Article';

    protected static ?string $pluralModelLabel = 'News Articles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Article Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('news.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('news.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(News::class, 'slug', ignoreRecord: true),
                        Forms\Components\Textarea::make('excerpt')
                            ->label(__('news.fields.excerpt'))
                            ->maxLength(500)
                            ->rows(3),
                        Forms\Components\RichEditor::make('content')
                            ->label(__('news.fields.content'))
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('news.fields.published_at'))
                            ->default(now()),
                        Forms\Components\TextInput::make('author_name')
                            ->label(__('news.fields.author_name'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('author_email')
                            ->label(__('news.fields.author_email'))
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('news.fields.is_visible'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('news.fields.is_featured')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('SEO & Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label(__('news.fields.meta_title'))
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label(__('news.fields.meta_description'))
                            ->maxLength(500)
                            ->rows(3),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label(__('news.fields.meta_keywords'))
                            ->maxLength(255),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Categories & Tags')
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->label(__('news.fields.categories'))
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload(),
                        Forms\Components\Select::make('tags')
                            ->label(__('news.fields.tags'))
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label(__('news.fields.featured_image'))
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('news.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('author_name')
                    ->label(__('news.fields.author_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label(__('news.fields.categories'))
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('news.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('news.fields.is_visible'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('news.fields.is_featured'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label(__('news.fields.view_count'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('news.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('news.fields.is_visible')),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('news.fields.is_featured')),
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label(__('news.filters.published_from')),
                        Forms\Components\DatePicker::make('published_until')
                            ->label(__('news.filters.published_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Article Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('news.fields.title')),
                        Infolists\Components\TextEntry::make('slug')
                            ->label(__('news.fields.slug'))
                            ->copyable(),
                        Infolists\Components\TextEntry::make('excerpt')
                            ->label(__('news.fields.excerpt'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('content')
                            ->label(__('news.fields.content'))
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Publishing Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('author_name')
                            ->label(__('news.fields.author_name')),
                        Infolists\Components\TextEntry::make('author_email')
                            ->label(__('news.fields.author_email')),
                        Infolists\Components\TextEntry::make('published_at')
                            ->label(__('news.fields.published_at'))
                            ->dateTime(),
                        Infolists\Components\IconEntry::make('is_visible')
                            ->label(__('news.fields.is_visible'))
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_featured')
                            ->label(__('news.fields.is_featured'))
                            ->boolean(),
                        Infolists\Components\TextEntry::make('view_count')
                            ->label(__('news.fields.view_count'))
                            ->numeric(),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('Categories & Tags')
                    ->schema([
                        Infolists\Components\TextEntry::make('categories.name')
                            ->label(__('news.fields.categories'))
                            ->badge()
                            ->separator(','),
                        Infolists\Components\TextEntry::make('tags.name')
                            ->label(__('news.fields.tags'))
                            ->badge()
                            ->separator(','),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
