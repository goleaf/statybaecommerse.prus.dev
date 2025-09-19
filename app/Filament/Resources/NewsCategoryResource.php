<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NewsCategoryResource\Pages;
use App\Models\NewsCategory;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * NewsCategoryResource
 *
 * Filament v4 resource for NewsCategory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    /**
     * @var UnitEnum|string|null
     */
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Content;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('news.categories');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return __('news.navigation_group');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('news.categories');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('news.category');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('news.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('news.category_name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                    $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('news.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('news.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('news.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('parent_id')
                                ->label(__('news.parent_category'))
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload(),
                            TextInput::make('sort_order')
                                ->label(__('news.sort_order'))
                                ->numeric()
                                ->default(0),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_visible')
                                ->label(__('news.is_visible'))
                                ->default(true),
                            ColorPicker::make('color')
                                ->label(__('news.color'))
                                ->default('#3B82F6'),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('news.category_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('parent.name')
                    ->label(__('news.parent_category'))
                    ->badge()
                    ->color('gray')
                    ->placeholder(__('news.no_parent')),
                TextColumn::make('description')
                    ->label(__('news.description'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('is_visible')
                    ->label(__('news.status'))
                    ->formatStateUsing(fn(bool $state): string => $state ? __('news.visible') : __('news.hidden'))
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ]),
                TextColumn::make('sort_order')
                    ->label(__('news.sort_order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('news_count')
                    ->label(__('news.news_count'))
                    ->counts('news')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('news.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('news.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label(__('news.is_visible'))
                    ->trueLabel(__('news.visible_only'))
                    ->falseLabel(__('news.hidden_only'))
                    ->native(false),
                Filter::make('has_parent')
                    ->label(__('news.has_parent'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('parent_id')),
                Filter::make('no_parent')
                    ->label(__('news.no_parent'))
                    ->query(fn(Builder $query): Builder => $query->whereNull('parent_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
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
