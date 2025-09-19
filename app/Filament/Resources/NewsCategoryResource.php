<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsCategoryResource\Pages;
use App\Models\NewsCategory;
use Filament\Schemas\Schema;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Infolists;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'News Category';

    protected static ?string $pluralModelLabel = 'News Categories';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('news_categories.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('news_categories.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(NewsCategory::class, 'slug', ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label(__('news_categories.fields.description'))
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Hierarchy & Display')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label(__('news_categories.fields.parent_id'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('news_categories.fields.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('color')
                            ->label(__('news_categories.fields.color'))
                            ->maxLength(7)
                            ->placeholder('#000000'),
                        Forms\Components\TextInput::make('icon')
                            ->label(__('news_categories.fields.icon'))
                            ->maxLength(255)
                            ->placeholder('heroicon-o-tag'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Visibility')
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('news_categories.fields.is_visible'))
                            ->default(true),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('news_categories.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('news_categories.fields.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('news_categories.fields.parent'))
                    ->sortable()
                    ->placeholder('Root'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('news_categories.fields.sort_order'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('news_categories.fields.color')),
                Tables\Columns\TextColumn::make('icon')
                    ->label(__('news_categories.fields.icon'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('news_categories.fields.is_visible'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('news_count')
                    ->label(__('news_categories.fields.news_count'))
                    ->counts('news')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('news_categories.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->label(__('news_categories.filters.parent')),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('news_categories.fields.is_visible')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Category Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label(__('news_categories.fields.name')),
                        Infolists\Components\TextEntry::make('slug')
                            ->label(__('news_categories.fields.slug'))
                            ->copyable(),
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('news_categories.fields.description'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('parent.name')
                            ->label(__('news_categories.fields.parent'))
                            ->placeholder('Root'),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Display Settings')
                    ->schema([
                        Infolists\Components\TextEntry::make('sort_order')
                            ->label(__('news_categories.fields.sort_order'))
                            ->numeric(),
                        Infolists\Components\TextEntry::make('color')
                            ->label(__('news_categories.fields.color'))
                            ->color(fn($state) => $state),
                        Infolists\Components\TextEntry::make('icon')
                            ->label(__('news_categories.fields.icon')),
                        Infolists\Components\IconEntry::make('is_visible')
                            ->label(__('news_categories.fields.is_visible'))
                            ->boolean(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('news_count')
                            ->label(__('news_categories.fields.news_count'))
                            ->numeric(),
                        Infolists\Components\TextEntry::make('children_count')
                            ->label(__('news_categories.fields.children_count'))
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
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
            'index' => Pages\ListNewsCategories::route('/'),
            'create' => Pages\CreateNewsCategory::route('/create'),
            'view' => Pages\ViewNewsCategory::route('/{record}'),
            'edit' => Pages\EditNewsCategory::route('/{record}/edit'),
        ];
    }
}
