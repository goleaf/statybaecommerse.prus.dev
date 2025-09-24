<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AttributeValueResource\Pages;
use App\Filament\Resources\AttributeValueResource\Relations\ProductsRelationManager as AttributeValueProductsRelationManager;
use App\Filament\Resources\AttributeValueResource\Relations\VariantsRelationManager as AttributeValueVariantsRelationManager;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Filament\Schemas\Components\Tabs\Tabs as SchemaTabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use UnitEnum;

final class AttributeValueResource extends Resource
{
    protected static ?string $model = AttributeValue::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return NavigationGroup::Products;
    }

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'value';

    protected static ?string $navigationLabel = 'Attribute Values';

    protected static ?string $modelLabel = 'Attribute Value';

    protected static ?string $pluralModelLabel = 'Attribute Values';

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('attribute_values.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('attribute_values.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaTabs::make('Attribute Value')
                ->tabs([
                    SchemaTab::make(__('attribute_values.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            SchemaSection::make(__('attribute_values.basic_information'))
                                ->description(__('attribute_values.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('attribute_id')
                                                ->label(__('attribute_values.attribute'))
                                                ->relationship('attribute', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if ($state) {
                                                        $attribute = Attribute::find($state);
                                                        if ($attribute) {
                                                            $set('attribute_name', $attribute->name);
                                                            $set('attribute_type', $attribute->type);
                                                        }
                                                    }
                                                }),
                                            TextInput::make('attribute_name')
                                                ->label(__('attribute_values.attribute_name'))
                                                ->disabled(),
                                            Select::make('attribute_value_type')
                                                ->label(__('attribute_values.attribute_value_type'))
                                                ->options([
                                                    'text' => __('attribute_values.types.text'),
                                                    'number' => __('attribute_values.types.number'),
                                                    'color' => __('attribute_values.types.color'),
                                                    'image' => __('attribute_values.types.image'),
                                                ])
                                                ->required(),
                                        ]),
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('valueable_type')
                                                ->label(__('attribute_values.valueable_type'))
                                                ->options([
                                                    'product' => __('attribute_values.types.product'),
                                                    'product_variant' => __('attribute_values.types.product_variant'),
                                                ])
                                                ->live()
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    $set('valueable_id', null);
                                                }),
                                            Select::make('valueable_id')
                                                ->label(__('attribute_values.valueable_item'))
                                                ->options(function (Get $get) {
                                                    $type = $get('valueable_type');
                                                    if ($type === 'product') {
                                                        return Product::pluck('name', 'id');
                                                    } elseif ($type === 'product_variant') {
                                                        return ProductVariant::pluck('name', 'id');
                                                    }

                                                    return [];
                                                })
                                                ->live()
                                                ->searchable(),
                                        ]),
                                ]),
                        ]),
                    SchemaTab::make(__('attribute_values.value_information'))
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            SchemaSection::make(__('attribute_values.value_information'))
                                ->description(__('attribute_values.value_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            TextInput::make('value')
                                                ->label(__('attribute_values.value'))
                                                ->maxLength(255)
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if (empty($state)) {
                                                        $set('slug', null);
                                                    } else {
                                                        $set('slug', Str::slug($state));
                                                    }
                                                }),
                                            TextInput::make('display_value')
                                                ->label(__('attribute_values.display_value'))
                                                ->helperText(__('attribute_values.display_value_help'))
                                                ->maxLength(255),
                                        ]),
                                    TextInput::make('slug')
                                        ->label(__('attributes.slug'))
                                        ->maxLength(255)
                                        ->unique(AttributeValue::class, 'slug', ignoreRecord: true)
                                        ->helperText(__('attributes.slug_auto_generated')),
                                    Textarea::make('description')
                                        ->label(__('attribute_values.description'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    SchemaTab::make(__('attribute_values.settings'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            SchemaSection::make(__('attribute_values.settings'))
                                ->description(__('attribute_values.settings'))
                                ->schema([
                                    SchemaGrid::make(3)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label(__('attribute_values.is_active'))
                                                ->default(true)
                                                ->helperText(__('attribute_values.is_active_help')),
                                            Toggle::make('is_default')
                                                ->label(__('attribute_values.is_default'))
                                                ->helperText(__('attribute_values.is_default_help')),
                                            Toggle::make('is_searchable')
                                                ->label(__('attribute_values.is_searchable'))
                                                ->default(false)
                                                ->helperText(__('attribute_values.is_searchable_help')),
                                        ]),
                                    SchemaGrid::make(2)
                                        ->schema([
                                            TextInput::make('sort_order')
                                                ->label(__('attribute_values.sort_order'))
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->helperText(__('attribute_values.sort_order_help')),
                                            TextInput::make('color_code')
                                                ->label(__('attributes.color'))
                                                ->maxLength(7)
                                                ->helperText(__('attributes.color_help')),
                                        ]),
                                ]),
                        ]),
                    SchemaTab::make(__('attributes.meta_data'))
                        ->icon('heroicon-o-code-bracket')
                        ->schema([
                            SchemaSection::make(__('attributes.meta_data'))
                                ->description(__('attributes.meta_data_help'))
                                ->schema([
                                    KeyValue::make('metadata')
                                        ->label(__('attributes.meta_data'))
                                        ->keyLabel(__('attributes.key'))
                                        ->valueLabel(__('attributes.value'))
                                        ->helperText(__('attributes.meta_data_help')),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('attribute.name')
                    ->label(__('attribute_values.attribute'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue')
                    ->copyable()
                    ->copyMessage('Attribute name copied')
                    ->copyMessageDuration(1500),
                BadgeColumn::make('valueable_type')
                    ->label(__('attribute_values.type'))
                    ->formatStateUsing(fn (string $state): string => __("attribute_values.types.{$state}"))
                    ->colors([
                        'success' => 'product',
                        'warning' => 'product_variant',
                    ])
                    ->icons([
                        'heroicon-o-cube' => 'product',
                        'heroicon-o-squares-2x2' => 'product_variant',
                    ]),
                TextColumn::make('valueable.name')
                    ->label(__('attribute_values.item'))
                    ->limit(50)
                    ->searchable()
                    ->sortable()
                    ->url(fn (AttributeValue $record): string => match ($record->valueable_type) {
                        'product' => route('filament.admin.resources.products.view', $record->valueable_id),
                        'product_variant' => route('filament.admin.resources.product-variants.view', $record->valueable_id),
                        default => '#',
                    })
                    ->openUrlInNewTab(),
                TextColumn::make('value')
                    ->label(__('attribute_values.value'))
                    ->limit(50)
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Value copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('display_value')
                    ->label(__('attribute_values.display_value'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('attribute_values.description'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                ColorColumn::make('color_code')
                    ->label(__('attributes.color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image')
                    ->label(__('attribute_values.image'))
                    ->circular()
                    ->size(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('hex_color')
                    ->label(__('attributes.hex_color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('attribute_values.products_count'))
                    ->counts('products')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('variants_count')
                    ->label(__('attribute_values.variants_count'))
                    ->counts('variants')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                ToggleColumn::make('is_active')
                    ->label(__('attribute_values.is_active'))
                    ->sortable()
                    ->toggleable(),
                ToggleColumn::make('is_default')
                    ->label(__('attribute_values.is_default'))
                    ->sortable()
                    ->toggleable(),
                ToggleColumn::make('is_searchable')
                    ->label(__('attribute_values.is_searchable'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->label(__('attribute_values.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('attribute_values.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('attribute_values.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('attribute_id')
                    ->relationship('attribute', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('valueable_type')
                    ->label(__('attribute_values.valueable_type'))
                    ->options([
                        'product' => __('attribute_values.types.product'),
                        'product_variant' => __('attribute_values.types.product_variant'),
                    ])
                    ->multiple(),
                TernaryFilter::make('is_active')
                    ->label(__('attribute_values.is_active'))
                    ->trueLabel(__('attribute_values.active_only'))
                    ->falseLabel(__('attribute_values.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->label(__('attribute_values.is_default'))
                    ->trueLabel(__('attribute_values.default_only'))
                    ->falseLabel(__('attribute_values.non_default_only'))
                    ->native(false),
                TernaryFilter::make('is_searchable')
                    ->label(__('attribute_values.is_searchable'))
                    ->trueLabel(__('attribute_values.searchable_only'))
                    ->falseLabel(__('attribute_values.not_searchable'))
                    ->native(false),
                Filter::make('has_description')
                    ->label(__('attribute_values.has_description'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('description'))
                    ->toggle(),
                Filter::make('has_display_value')
                    ->label(__('attribute_values.has_display_value'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('display_value'))
                    ->toggle(),
                Filter::make('has_image')
                    ->label(__('attribute_values.has_image'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('image'))
                    ->toggle(),
                Filter::make('has_color')
                    ->label(__('attribute_values.has_color'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('color_code'))
                    ->toggle(),
                Filter::make('with_products')
                    ->label(__('attribute_values.with_products'))
                    ->query(fn (Builder $query): Builder => $query->has('products'))
                    ->toggle(),
                Filter::make('with_variants')
                    ->label(__('attribute_values.with_variants'))
                    ->query(fn (Builder $query): Builder => $query->has('variants'))
                    ->toggle(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('value')
                            ->label(__('attribute_values.value')),
                        TextConstraint::make('display_value')
                            ->label(__('attribute_values.display_value')),
                        NumberConstraint::make('sort_order')
                            ->label(__('attribute_values.sort_order')),
                        DateConstraint::make('created_at')
                            ->label(__('attribute_values.created_at')),
                        BooleanConstraint::make('is_active')
                            ->label(__('attribute_values.is_active')),
                        BooleanConstraint::make('is_default')
                            ->label(__('attribute_values.is_default')),
                        BooleanConstraint::make('is_searchable')
                            ->label(__('attribute_values.is_searchable')),
                        SelectConstraint::make('attribute_id')
                            ->label(__('attribute_values.attribute'))
                            ->options(fn () => \App\Models\Attribute::pluck('name', 'id')->toArray()),
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->slideOver(),
                EditAction::make()
                    ->slideOver(),
                Action::make('toggle_active')
                    ->label(fn (AttributeValue $record): string => $record->is_active ? __('attribute_values.deactivate') : __('attribute_values.activate'))
                    ->icon(fn (AttributeValue $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (AttributeValue $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (AttributeValue $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('attribute_values.activated_successfully') : __('attribute_values.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('attribute_values.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (AttributeValue $record): bool => ! $record->is_default)
                    ->action(function (AttributeValue $record): void {
                        // Remove default from other values for the same attribute and item
                        AttributeValue::where('attribute_id', $record->attribute_id)
                            ->where('valueable_type', $record->valueable_type)
                            ->where('valueable_id', $record->valueable_id)
                            ->where('is_default', true)
                            ->update(['is_default' => false]);

                        // Set this value as default
                        $record->update(['is_default' => true]);

                        Notification::make()
                            ->title(__('attribute_values.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('attributes.duplicate_attribute'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (AttributeValue $record): void {
                        $duplicate = $record->replicate();
                        $duplicate->value = $record->value.' (Copy)';
                        $duplicate->slug = $record->slug.'-copy';
                        $duplicate->is_default = false;
                        $duplicate->save();

                        Notification::make()
                            ->title(__('attributes.attribute_duplicated'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('attribute_values.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('attribute_values.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('attribute_values.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('attribute_values.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('set_searchable')
                        ->label(__('attribute_values.set_searchable'))
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_searchable' => true]);
                            Notification::make()
                                ->title(__('attribute_values.bulk_set_searchable_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('export')
                        ->label(__('attribute_values.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('attribute_values.exported_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('duplicate')
                        ->label(__('attribute_values.duplicate_selected'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                $duplicate = $record->replicate();
                                $duplicate->value = $record->value.' (Copy)';
                                $duplicate->slug = $record->slug.'-copy';
                                $duplicate->is_default = false;
                                $duplicate->save();
                            });
                            Notification::make()
                                ->title(__('attribute_values.bulk_duplicated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('sort_order')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            AttributeValueProductsRelationManager::class,
            AttributeValueVariantsRelationManager::class,
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'view' => Pages\ViewAttributeValue::route('/{record}'),
            'edit' => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }
}
