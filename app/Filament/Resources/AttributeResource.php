<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Models\Attribute;
use App\Services\MultiLanguageTabService;
use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use BackedEnum;
use UnitEnum;

final class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Attribute Settings (Non-translatable)
                Forms\Components\Section::make(__('translations.attribute_settings'))
                    ->components([
                        Forms\Components\Select::make('type')
                            ->label(__('translations.type'))
                            ->options([
                                'text' => __('translations.text'),
                                'number' => __('translations.number'),
                                'boolean' => __('translations.boolean'),
                                'select' => __('translations.select'),
                                'multiselect' => __('translations.multiselect'),
                                'color' => __('translations.color'),
                                'date' => __('translations.date'),
                            ])
                            ->required()
                            ->default('text'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('translations.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_required')
                            ->label(__('translations.required'))
                            ->default(false),
                        Forms\Components\Toggle::make('is_filterable')
                            ->label(__('translations.filterable'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_searchable')
                            ->label(__('translations.searchable'))
                            ->default(false),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('translations.enabled'))
                            ->default(true),
                    ])
                    ->columns(3),
                // Multilanguage Tabs for Translatable Content
                Tabs::make('attribute_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'attribute_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.attribute_name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'slug' => [
                                    'type' => 'text',
                                    'label' => __('translations.slug'),
                                    'required' => true,
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.slug_auto_generated'),
                                ],
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.attribute_description'),
                                    'maxLength' => 1000,
                                    'rows' => 3,
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('attribute_tab')
                    ->contained(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('translations.slug'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('translations.type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'text' => 'gray',
                        'number' => 'blue',
                        'boolean' => 'green',
                        'select' => 'yellow',
                        'multiselect' => 'orange',
                        'color' => 'purple',
                        'date' => 'red',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('values_count')
                    ->counts('values')
                    ->label(__('translations.values'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('translations.required'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('translations.filterable'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_searchable')
                    ->label(__('translations.searchable'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('translations.type'))
                    ->options([
                        'text' => __('translations.text'),
                        'number' => __('translations.number'),
                        'boolean' => __('translations.boolean'),
                        'select' => __('translations.select'),
                        'multiselect' => __('translations.multiselect'),
                        'color' => __('translations.color'),
                        'date' => __('translations.date'),
                    ]),
                Tables\Filters\Filter::make('required')
                    ->label(__('translations.required_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_required', true)),
                Tables\Filters\Filter::make('filterable')
                    ->label(__('translations.filterable_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_filterable', true)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'view' => Pages\ViewAttribute::route('/{record}'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
