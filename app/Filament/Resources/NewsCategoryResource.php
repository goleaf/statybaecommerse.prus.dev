<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsCategoryResource\Pages;
use App\Models\NewsCategory;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Content';
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'News Category';

    protected static ?string $pluralModelLabel = 'News Categories';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('news_categories.sections.category_information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('news_categories.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state)))
                        ->placeholder(__('news_categories.fields.name'))
                        ->helperText(__('news_categories.fields.name').' '.__('for all languages')),
                    TextInput::make('slug')
                        ->label(__('news_categories.fields.slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(NewsCategory::class, 'slug', ignoreRecord: true)
                        ->placeholder(__('news_categories.fields.slug'))
                        ->helperText(__('URL friendly version of name')),
                    Textarea::make('description')
                        ->label(__('news_categories.fields.description'))
                        ->maxLength(1000)
                        ->rows(3)
                        ->placeholder(__('news_categories.fields.description'))
                        ->helperText(__('Brief description of the category')),
                ])
                ->columns(2),
            Section::make(__('news_categories.sections.hierarchy_display'))
                ->schema([
                    Select::make('parent_id')
                        ->label(__('news_categories.fields.parent_id'))
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Select parent category'))
                        ->helperText(__('Optional parent category for hierarchy')),
                    TextInput::make('sort_order')
                        ->label(__('news_categories.fields.sort_order'))
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(9999)
                        ->placeholder('0')
                        ->helperText(__('Lower numbers appear first')),
                    ColorPicker::make('color')
                        ->label(__('news_categories.fields.color'))
                        ->placeholder('#000000')
                        ->helperText(__('Category color for UI display')),
                    Select::make('icon')
                        ->label(__('news_categories.fields.icon'))
                        ->options([
                            'heroicon-o-tag' => 'Tag',
                            'heroicon-o-document-text' => 'Document',
                            'heroicon-o-newspaper' => 'Newspaper',
                            'heroicon-o-folder' => 'Folder',
                            'heroicon-o-rectangle-stack' => 'Stack',
                            'heroicon-o-squares-2x2' => 'Grid',
                            'heroicon-o-bookmark' => 'Bookmark',
                            'heroicon-o-star' => 'Star',
                            'heroicon-o-fire' => 'Fire',
                            'heroicon-o-bolt' => 'Bolt',
                            'heroicon-o-light-bulb' => 'Light Bulb',
                            'heroicon-o-cog' => 'Settings',
                            'heroicon-o-wrench-screwdriver' => 'Tools',
                            'heroicon-o-building-office' => 'Building',
                            'heroicon-o-home' => 'Home',
                        ])
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Select icon'))
                        ->helperText(__('Icon to display with category')),
                ])
                ->columns(2),
            Section::make(__('news_categories.sections.visibility'))
                ->schema([
                    Toggle::make('is_visible')
                        ->label(__('news_categories.fields.is_visible'))
                        ->default(true)
                        ->helperText(__('Whether this category is visible to users')),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('news_categories.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->tooltip(__('Click to copy name')),
                TextColumn::make('slug')
                    ->label(__('news_categories.fields.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->tooltip(__('Click to copy slug')),
                TextColumn::make('parent.name')
                    ->label(__('news_categories.fields.parent'))
                    ->sortable()
                    ->placeholder(__('Root'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('sort_order')
                    ->label(__('news_categories.fields.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                ColorColumn::make('color')
                    ->label(__('news_categories.fields.color'))
                    ->copyable()
                    ->tooltip(__('Click to copy color code')),
                TextColumn::make('icon')
                    ->label(__('news_categories.fields.icon'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->tooltip(__('Click to copy icon name')),
                IconColumn::make('is_visible')
                    ->label(__('news_categories.fields.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('news_count')
                    ->label(__('news_categories.fields.news_count'))
                    ->counts('news')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                TextColumn::make('children_count')
                    ->label(__('news_categories.fields.children_count'))
                    ->counts('children')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('news_categories.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->label(__('news_categories.filters.parent'))
                    ->placeholder(__('All categories'))
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('is_visible')
                    ->label(__('news_categories.fields.is_visible'))
                    ->placeholder(__('All categories'))
                    ->trueLabel(__('Visible only'))
                    ->falseLabel(__('Hidden only')),
                SelectFilter::make('has_news')
                    ->label(__('Has News'))
                    ->options([
                        'with_news' => __('With News'),
                        'without_news' => __('Without News'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'with_news' => $query->has('news'),
                            'without_news' => $query->doesntHave('news'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('has_children')
                    ->label(__('Has Children'))
                    ->options([
                        'with_children' => __('With Children'),
                        'without_children' => __('Without Children'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'with_children' => $query->has('children'),
                            'without_children' => $query->doesntHave('children'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->color('info'),
                EditAction::make()
                    ->color('warning'),
                DeleteAction::make()
                    ->color('danger')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Infolists\Components\Section::make(__('news_categories.sections.category_details'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('news_categories.fields.name'))
                            ->size('lg')
                            ->weight('bold')
                            ->copyable(),
                        TextEntry::make('slug')
                            ->label(__('news_categories.fields.slug'))
                            ->copyable()
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('description')
                            ->label(__('news_categories.fields.description'))
                            ->columnSpanFull()
                            ->markdown()
                            ->placeholder(__('No description provided')),
                        TextEntry::make('parent.name')
                            ->label(__('news_categories.fields.parent'))
                            ->placeholder(__('Root'))
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make(__('news_categories.sections.display_settings'))
                    ->schema([
                        TextEntry::make('sort_order')
                            ->label(__('news_categories.fields.sort_order'))
                            ->numeric()
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('color')
                            ->label(__('news_categories.fields.color'))
                            ->color(fn ($state) => $state)
                            ->copyable()
                            ->badge(),
                        TextEntry::make('icon')
                            ->label(__('news_categories.fields.icon'))
                            ->badge()
                            ->color('secondary')
                            ->copyable(),
                        IconEntry::make('is_visible')
                            ->label(__('news_categories.fields.is_visible'))
                            ->boolean()
                            ->trueIcon('heroicon-o-eye')
                            ->falseIcon('heroicon-o-eye-slash')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make(__('news_categories.sections.statistics'))
                    ->schema([
                        TextEntry::make('news_count')
                            ->label(__('news_categories.fields.news_count'))
                            ->numeric()
                            ->badge()
                            ->color('info'),
                        TextEntry::make('children_count')
                            ->label(__('news_categories.fields.children_count'))
                            ->numeric()
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('created_at')
                            ->label(__('news_categories.fields.created_at'))
                            ->dateTime()
                            ->since()
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('updated_at')
                            ->label(__('news_categories.fields.updated_at'))
                            ->dateTime()
                            ->since()
                            ->badge()
                            ->color('gray'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\NewsCategoryResource\RelationManagers\NewsRelationManager::class,
            \App\Filament\Resources\NewsCategoryResource\RelationManagers\ChildrenRelationManager::class,
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
