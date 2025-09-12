<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeValueResource\Pages;
use App\Filament\Resources\AttributeValueResource\RelationManagers;
use App\Filament\Resources\AttributeValueResource\Widgets;
use App\Models\Attribute;
use UnitEnum;
use App\Models\AttributeValue;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class AttributeValueResource extends Resource
{
    protected static ?string $model = AttributeValue::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-tag';

    /**
     * @var string|\BackedEnum|null
     */
    protected static UnitEnum|string|null $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'value';

    public static function getModelLabel(): string
    {
        return __('attributes.attribute_value');
    }

    public static function getPluralModelLabel(): string
    {
        return __('attributes.attribute_values');
    }

    public static function getNavigationLabel(): string
    {
        return __('attributes.attribute_values');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Attribute Value')
                    ->tabs([
                        Tabs\Tab::make(__('attributes.basic_information'))
                            ->schema([
                                Section::make(__('attributes.basic_information'))
                                    ->schema([
                                        Forms\Components\Select::make('attribute_id')
                                            ->label(__('attributes.attribute'))
                                            ->relationship('attribute', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('attributes.name'))
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('slug')
                                                    ->label(__('attributes.slug'))
                                                    ->maxLength(255),
                                                Forms\Components\Select::make('type')
                                                    ->label(__('attributes.type'))
                                                    ->options([
                                                        'text' => __('attributes.text'),
                                                        'number' => __('attributes.number'),
                                                        'select' => __('attributes.select'),
                                                        'multiselect' => __('attributes.multiselect'),
                                                        'checkbox' => __('attributes.checkbox'),
                                                        'radio' => __('attributes.radio'),
                                                        'color' => __('attributes.color'),
                                                        'image' => __('attributes.image'),
                                                    ])
                                                    ->required(),
                                                Forms\Components\Toggle::make('is_required')
                                                    ->label(__('attributes.required')),
                                                Forms\Components\Toggle::make('is_enabled')
                                                    ->label(__('attributes.enabled'))
                                                    ->default(true),
                                            ]),
                                        Forms\Components\TextInput::make('value')
                                            ->label(__('attributes.value'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live()
                                            ->afterStateUpdated(fn(Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                                        Forms\Components\TextInput::make('slug')
                                            ->label(__('attributes.slug'))
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true),
                                        Forms\Components\Textarea::make('description')
                                            ->label(__('attributes.description'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Forms\Components\ColorPicker::make('color_code')
                                            ->label(__('attributes.color_code'))
                                            ->helperText(__('attributes.color_code_help')),
                                        Forms\Components\TextInput::make('sort_order')
                                            ->label(__('attributes.sort_order'))
                                            ->numeric()
                                            ->default(0),
                                        Grid::make(3)
                                            ->schema([
                                                Forms\Components\Toggle::make('is_enabled')
                                                    ->label(__('attributes.enabled'))
                                                    ->default(true),
                                                Forms\Components\Toggle::make('is_required')
                                                    ->label(__('attributes.required')),
                                                Forms\Components\Toggle::make('is_default')
                                                    ->label(__('attributes.default')),
                                            ]),
                                    ])
                                    ->columns(2),
                            ]),
                        Tabs\Tab::make(__('translations.translations'))
                            ->schema([
                                Section::make(__('translations.translations'))
                                    ->schema([
                                        Repeater::make('translations')
                                            ->label(__('translations.translations'))
                                            ->relationship('translations')
                                            ->schema([
                                                Forms\Components\Select::make('locale')
                                                    ->label(__('translations.locale'))
                                                    ->options([
                                                        'lt' => __('translations.lithuanian'),
                                                        'en' => __('translations.english'),
                                                        'de' => __('translations.german'),
                                                    ])
                                                    ->required()
                                                    ->searchable(),
                                                Forms\Components\TextInput::make('value')
                                                    ->label(__('attributes.value'))
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('description')
                                                    ->label(__('attributes.description'))
                                                    ->rows(2),
                                                Forms\Components\KeyValue::make('meta_data')
                                                    ->label(__('attributes.meta_data'))
                                                    ->keyLabel(__('attributes.meta_key'))
                                                    ->valueLabel(__('attributes.meta_value')),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['locale'] ?? null)
                                            ->addActionLabel(__('translations.add_translation'))
                                            ->defaultItems(0),
                                    ]),
                            ]),
                        Tabs\Tab::make(__('attributes.meta_data'))
                            ->schema([
                                Section::make(__('attributes.meta_data'))
                                    ->schema([
                                        Forms\Components\KeyValue::make('meta_data')
                                            ->label(__('attributes.meta_data'))
                                            ->keyLabel(__('attributes.meta_key'))
                                            ->valueLabel(__('attributes.meta_value'))
                                            ->helperText(__('attributes.meta_data_help')),
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
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label(__('attributes.attribute'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('attributes.value'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('attributes.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('attributes.description'))
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\ColorColumn::make('color_code')
                    ->label(__('attributes.color'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('attributes.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('attributes.enabled'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('attributes.required'))
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('attributes.default'))
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('attributes.products_count'))
                    ->counts('products')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('variants_count')
                    ->label(__('attributes.variants_count'))
                    ->counts('variants')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('translations_count')
                    ->label(__('translations.translations_count'))
                    ->counts('translations')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('attribute_id')
                    ->label(__('attributes.attribute'))
                    ->relationship('attribute', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_enabled')
                    ->label(__('attributes.enabled'))
                    ->boolean()
                    ->trueLabel(__('attributes.enabled_only'))
                    ->falseLabel(__('attributes.disabled_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->label(__('attributes.required'))
                    ->boolean()
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->label(__('attributes.default'))
                    ->boolean()
                    ->native(false),
                Filter::make('with_color')
                    ->label(__('attributes.with_color'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('color_code')),
                Filter::make('with_description')
                    ->label(__('attributes.with_description'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('description')),
                Tables\Filters\TrashedFilter::make(),
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
            ->groups([
                Group::make('attribute.name')
                    ->label(__('attributes.by_attribute'))
                    ->collapsible(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->defaultGroup('attribute.name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'view' => Pages\ViewAttributeValue::route('/{record}'),
            'edit' => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\AttributeValueStatsWidget::class,
            Widgets\AttributeValueChartWidget::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['attribute']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['value', 'slug', 'description', 'attribute.name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('attributes.attribute') => $record->attribute->name ?? null,
            __('attributes.value') => $record->value,
            __('attributes.sort_order') => $record->sort_order,
        ];
    }
}
