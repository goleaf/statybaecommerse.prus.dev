<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use BackedEnum;
use App\Filament\Resources\AttributeResource\RelationManagers;
use App\Filament\Resources\AttributeResource\Widgets;
use App\Models\Attribute;
use App\Services\MultiLanguageTabService;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\IconEntry;
use Filament\Schemas\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use App\Enums\NavigationGroup;
use UnitEnum;

final /**
 * AttributeResource
 * 
 * Filament resource for admin panel management.
 */
class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }

    public static function getNavigationLabel(): string
    {
        return __('attributes.attributes');
    }

    public static function getPluralModelLabel(): string
    {
        return __('attributes.attributes');
    }

    public static function getModelLabel(): string
    {
        return __('attributes.attribute');
    }

    public static function form(Schema $schema): Schema {
        return $schema->components([
                // Attribute Settings (Non-translatable)
                Section::make(__('attributes.attribute_settings'))
                    ->description(__('attributes.attribute_settings_description'))
                    ->components([
                        Forms\Components\Select::make('type')
                            ->label(__('attributes.type'))
                            ->options([
                                'text' => __('attributes.text'),
                                'number' => __('attributes.number'),
                                'boolean' => __('attributes.boolean'),
                                'select' => __('attributes.select'),
                                'multiselect' => __('attributes.multiselect'),
                                'color' => __('attributes.color'),
                                'date' => __('attributes.date'),
                                'textarea' => __('attributes.textarea'),
                                'file' => __('attributes.file'),
                                'image' => __('attributes.image'),
                            ])
                            ->required()
                            ->default('text')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Set default values based on type
                                $set('default_value', match ($state) {
                                    'text', 'textarea' => '',
                                    'number' => 0,
                                    'boolean' => false,
                                    'select', 'multiselect' => null,
                                    'color' => '#000000',
                                    'date' => null,
                                    'file', 'image' => null,
                                    default => null,
                                });
                            }),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('attributes.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('attributes.sort_order_help')),

                        Forms\Components\TextInput::make('group_name')
                            ->label(__('attributes.group_name'))
                            ->placeholder(__('attributes.group_name_placeholder'))
                            ->helperText(__('attributes.group_name_help')),

                        Forms\Components\TextInput::make('icon')
                            ->label(__('attributes.icon'))
                            ->placeholder('heroicon-o-adjustments-horizontal')
                            ->helperText(__('attributes.icon_help')),

                        Forms\Components\ColorPicker::make('color')
                            ->label(__('attributes.color'))
                            ->default('#3B82F6')
                            ->helperText(__('attributes.color_help')),
                    ])
                    ->columns(3),

                // Boolean Settings
                Section::make(__('attributes.attribute_properties'))
                    ->description(__('attributes.attribute_properties_description'))
                    ->components([
                        Forms\Components\Toggle::make('is_required')
                            ->label(__('attributes.required'))
                            ->default(false)
                            ->helperText(__('attributes.required_help')),

                        Forms\Components\Toggle::make('is_filterable')
                            ->label(__('attributes.filterable'))
                            ->default(true)
                            ->helperText(__('attributes.filterable_help')),

                        Forms\Components\Toggle::make('is_searchable')
                            ->label(__('attributes.searchable'))
                            ->default(false)
                            ->helperText(__('attributes.searchable_help')),

                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('attributes.visible'))
                            ->default(true)
                            ->helperText(__('attributes.visible_help')),

                        Forms\Components\Toggle::make('is_editable')
                            ->label(__('attributes.editable'))
                            ->default(true)
                            ->helperText(__('attributes.editable_help')),

                        Forms\Components\Toggle::make('is_sortable')
                            ->label(__('attributes.sortable'))
                            ->default(true)
                            ->helperText(__('attributes.sortable_help')),

                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('attributes.enabled'))
                            ->default(true)
                            ->helperText(__('attributes.enabled_help')),
                    ])
                    ->columns(3),

                // Numeric Settings
                Section::make(__('attributes.numeric_settings'))
                    ->description(__('attributes.numeric_settings_description'))
                    ->components([
                        Forms\Components\TextInput::make('min_value')
                            ->label(__('attributes.min_value'))
                            ->numeric()
                            ->visible(fn (callable $get) => in_array($get('type'), ['number']))
                            ->helperText(__('attributes.min_value_help')),

                        Forms\Components\TextInput::make('max_value')
                            ->label(__('attributes.max_value'))
                            ->numeric()
                            ->visible(fn (callable $get) => in_array($get('type'), ['number']))
                            ->helperText(__('attributes.max_value_help')),

                        Forms\Components\TextInput::make('step_value')
                            ->label(__('attributes.step_value'))
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (callable $get) => in_array($get('type'), ['number']))
                            ->helperText(__('attributes.step_value_help')),

                        Forms\Components\TextInput::make('default_value')
                            ->label(__('attributes.default_value'))
                            ->visible(fn (callable $get) => ! in_array($get('type'), ['file', 'image']))
                            ->helperText(__('attributes.default_value_help')),
                    ])
                    ->columns(2),

                // Multilanguage content
                Tabs::make('attribute_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'attribute_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('attributes.attribute_name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'slug' => [
                                    'type' => 'text',
                                    'label' => __('attributes.slug'),
                                    'required' => true,
                                    'maxLength' => 255,
                                    'placeholder' => __('attributes.slug_auto_generated'),
                                ],
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('attributes.attribute_description'),
                                    'maxLength' => 1000,
                                    'rows' => 3,
                                ],
                                'placeholder' => [
                                    'type' => 'text',
                                    'label' => __('attributes.placeholder'),
                                    'maxLength' => 255,
                                ],
                                'help_text' => [
                                    'type' => 'textarea',
                                    'label' => __('attributes.help_text'),
                                    'maxLength' => 500,
                                    'rows' => 2,
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('attribute_tab'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $infolist
            ->components([
                Section::make(__('attributes.attribute_information'))
                    ->components([
                        TextEntry::make('name')
                            ->label(__('attributes.attribute_name')),
                        TextEntry::make('slug')
                            ->label(__('attributes.slug')),
                        TextEntry::make('description')
                            ->label(__('attributes.attribute_description'))
                            ->html(),
                        TextEntry::make('placeholder')
                            ->label(__('attributes.placeholder')),
                        TextEntry::make('help_text')
                            ->label(__('attributes.help_text'))
                            ->html(),
                    ])
                    ->columns(2),

                Section::make(__('attributes.attribute_settings'))
                    ->components([
                        TextEntry::make('type')
                            ->label(__('attributes.type'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'text' => __('attributes.text'),
                                'number' => __('attributes.number'),
                                'boolean' => __('attributes.boolean'),
                                'select' => __('attributes.select'),
                                'multiselect' => __('attributes.multiselect'),
                                'color' => __('attributes.color'),
                                'date' => __('attributes.date'),
                                'textarea' => __('attributes.textarea'),
                                'file' => __('attributes.file'),
                                'image' => __('attributes.image'),
                                default => ucfirst($state),
                            }),
                        TextEntry::make('group_name')
                            ->label(__('attributes.group_name')),
                        TextEntry::make('sort_order')
                            ->label(__('attributes.sort_order')),
                        TextEntry::make('default_value')
                            ->label(__('attributes.default_value')),
                    ])
                    ->columns(2),

                Section::make(__('attributes.attribute_properties'))
                    ->components([
                        IconEntry::make('is_required')
                            ->label(__('attributes.required'))
                            ->boolean(),
                        IconEntry::make('is_filterable')
                            ->label(__('attributes.filterable'))
                            ->boolean(),
                        IconEntry::make('is_searchable')
                            ->label(__('attributes.searchable'))
                            ->boolean(),
                        IconEntry::make('is_visible')
                            ->label(__('attributes.visible'))
                            ->boolean(),
                        IconEntry::make('is_editable')
                            ->label(__('attributes.editable'))
                            ->boolean(),
                        IconEntry::make('is_sortable')
                            ->label(__('attributes.sortable'))
                            ->boolean(),
                        IconEntry::make('is_enabled')
                            ->label(__('attributes.enabled'))
                            ->boolean(),
                        TextEntry::make('values_count')
                            ->label(__('attributes.values_count'))
                            ->state(fn (Attribute $record): int => $record->values()->count()),
                    ])
                    ->columns(3),

                Section::make(__('translations.timestamps'))
                    ->components([
                        TextEntry::make('created_at')
                            ->label(__('translations.created_at'))
                            ->date('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label(__('translations.updated_at'))
                            ->date('Y-m-d H:i:s'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('attributes.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('attributes.slug'))
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('attributes.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'gray',
                        'number' => 'blue',
                        'boolean' => 'green',
                        'select' => 'yellow',
                        'multiselect' => 'orange',
                        'color' => 'purple',
                        'date' => 'red',
                        'textarea' => 'indigo',
                        'file' => 'pink',
                        'image' => 'rose',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'text' => __('attributes.text'),
                        'number' => __('attributes.number'),
                        'boolean' => __('attributes.boolean'),
                        'select' => __('attributes.select'),
                        'multiselect' => __('attributes.multiselect'),
                        'color' => __('attributes.color'),
                        'date' => __('attributes.date'),
                        'textarea' => __('attributes.textarea'),
                        'file' => __('attributes.file'),
                        'image' => __('attributes.image'),
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('group_name')
                    ->label(__('attributes.group_name'))
                    ->searchable()
                    ->toggleable()
                    ->badge(),

                Tables\Columns\TextColumn::make('values_count')
                    ->counts('values')
                    ->label(__('attributes.values_count'))
                    ->sortable()
                    ->badge(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('attributes.required'))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('attributes.filterable'))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_searchable')
                    ->label(__('attributes.searchable'))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('attributes.enabled'))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('attributes.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('attributes.type'))
                    ->options([
                        'text' => __('attributes.text'),
                        'number' => __('attributes.number'),
                        'boolean' => __('attributes.boolean'),
                        'select' => __('attributes.select'),
                        'multiselect' => __('attributes.multiselect'),
                        'color' => __('attributes.color'),
                        'date' => __('attributes.date'),
                        'textarea' => __('attributes.textarea'),
                        'file' => __('attributes.file'),
                        'image' => __('attributes.image'),
                    ]),

                Tables\Filters\SelectFilter::make('group_name')
                    ->label(__('attributes.group_name'))
                    ->options(fn () => Attribute::distinct()->pluck('group_name', 'group_name')->filter())
                    ->searchable(),

                Tables\Filters\Filter::make('required')
                    ->label(__('attributes.required_only'))
                    ->query(fn (Builder $query): Builder => $query->where('is_required', true)),

                Tables\Filters\Filter::make('filterable')
                    ->label(__('attributes.filterable_only'))
                    ->query(fn (Builder $query): Builder => $query->where('is_filterable', true)),

                Tables\Filters\Filter::make('searchable')
                    ->label(__('attributes.searchable_only'))
                    ->query(fn (Builder $query): Builder => $query->where('is_searchable', true)),

                Tables\Filters\Filter::make('enabled')
                    ->label(__('attributes.enabled_only'))
                    ->query(fn (Builder $query): Builder => $query->where('is_enabled', true)),

                Tables\Filters\Filter::make('with_values')
                    ->label(__('attributes.with_values_only'))
                    ->query(fn (Builder $query): Builder => $query->has('values')),

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
            ->defaultSort('sort_order', 'asc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ValuesRelationManager::class,
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\AttributeStatsWidget::class,
            Widgets\AttributeTypesWidget::class,
            Widgets\AttributeUsageWidget::class,
            Widgets\AttributeAnalyticsWidget::class,
            Widgets\AttributePerformanceWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'view' => Pages\ViewAttribute::route('/{record}'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('attributes.type') => $record->type,
            __('attributes.group_name') => $record->group_name,
        ];
    }

    public static function getGlobalSearchResultUrl($record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }
}
