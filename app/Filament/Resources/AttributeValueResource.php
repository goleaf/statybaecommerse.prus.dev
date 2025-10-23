<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Support\Concerns\HasNav;
use Filament\Schemas\Schema;
use App\Enums\NavigationGroup;
use App\Filament\Resources\AttributeValueResource\Pages;
use App\Filament\Resources\AttributeValueResource\Relations\ProductsRelationManager as AttributeValueProductsRelationManager;
use App\Filament\Resources\AttributeValueResource\Relations\VariantsRelationManager as AttributeValueVariantsRelationManager;
use App\Models\AttributeValue;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use UnitEnum;

final class AttributeValueResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::Products->value;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'value';

    public static function getNavigationLabel(): string
    {
        return __('attribute_values.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('attribute_values.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('attribute_values.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        // Allow administrators to manage inactive and disabled records without
        // temporarily dropping global scopes that primarily serve storefront
        // queries.
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
                EnabledScope::class,
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('attribute_values.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('attribute_id')
                                ->label(__('attribute_values.attribute'))
                                ->relationship('attribute', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('value')
                                ->label(__('attribute_values.value'))
                                ->maxLength(255)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                    $set('slug', filled($state) ? Str::slug($state) : null);
                                }),
                            TextInput::make('slug')
                                ->label(__('attributes.slug'))
                                ->maxLength(255)
                                ->required()
                                ->unique(ignoreRecord: true),
                            TextInput::make('display_value')
                                ->label(__('attribute_values.display_value'))
                                ->helperText(__('attribute_values.display_value_help'))
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('attribute_values.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('attribute_values.settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('attribute_values.is_active'))
                                ->default(true)
                                ->helperText(__('attribute_values.is_active_help')),
                            Toggle::make('is_default')
                                ->label(__('attribute_values.is_default'))
                                ->helperText(__('attribute_values.is_default_help')),
                        ]),
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('attribute_values.sort_order'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->helperText(__('attribute_values.sort_order_help')),
                            TextInput::make('color_code')
                                ->label(__('attributes.color'))
                                ->maxLength(7)
                                ->helperText(__('attributes.color_help')),
                            TextInput::make('hex_color')
                                ->label(__('attributes.hex_color'))
                                ->maxLength(7),
                        ]),
                    TextInput::make('image')
                        ->label(__('attribute_values.image'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('attributes.meta_data'))
                ->schema([
                    KeyValue::make('metadata')
                        ->label(__('attributes.meta_data'))
                        ->keyLabel(__('attributes.key'))
                        ->valueLabel(__('attributes.value'))
                        ->helperText(__('attributes.meta_data_help')),
                ]),
        ]);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('attribute.name')
                    ->label(__('attribute_values.attribute'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue')
                    ->copyable()
                    ->copyMessage(__('attribute_values.attribute_copied'))
                    ->copyMessageDuration(1500),
                TextColumn::make('value')
                    ->label(__('attribute_values.value'))
                    ->limit(50)
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('attribute_values.value_copied'))
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
                TextColumn::make('hex_color')
                    ->label(__('attributes.hex_color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image')
                    ->label(__('attribute_values.image'))
                    ->circular()
                    ->size(40)
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
                    ->sortable(),
                ToggleColumn::make('is_default')
                    ->label(__('attribute_values.is_default'))
                    ->sortable(),
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
                Filter::make('has_description')
                    ->label(__('attribute_values.has_description'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('description')->where('description', '!=', ''))
                    ->toggle(),
                Filter::make('has_display_value')
                    ->label(__('attribute_values.has_display_value'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('display_value')->where('display_value', '!=', ''))
                    ->toggle(),
                Filter::make('has_image')
                    ->label(__('attribute_values.has_image'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('image')->where('image', '!=', ''))
                    ->toggle(),
                Filter::make('has_color')
                    ->label(__('attribute_values.has_color'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('color_code')->where('color_code', '!=', ''))
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
                        SelectConstraint::make('attribute_id')
                            ->label(__('attribute_values.attribute'))
                            ->options(fn (): array => AttributeValue::query()
                                ->with('attribute:id,name')
                                ->get()
                                ->pluck('attribute.name', 'attribute_id')
                                ->filter()
                                ->unique()
                                ->toArray()),
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->slideOver(),
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(fn (AttributeValue $record): string => $record->is_active
                        ? __('attribute_values.deactivate')
                        : __('attribute_values.activate'))
                    ->icon(fn (AttributeValue $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (AttributeValue $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (AttributeValue $record): void {
                        self::toggleActiveState($record);
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('attribute_values.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (AttributeValue $record): bool => ! $record->is_default)
                    ->action(function (AttributeValue $record): void {
                        self::setAsDefault($record);
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('attribute_values.duplicate_action'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (AttributeValue $record): void {
                        self::duplicateAttributeValue($record);
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
                            self::activateRecords($records);
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
                    BulkAction::make('duplicate')
                        ->label(__('attribute_values.duplicate_selected'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (AttributeValue $record): void {
                                self::duplicateAttributeValue($record, false);
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

    public static function getRelations(): array
    {
        return [
            AttributeValueProductsRelationManager::class,
            AttributeValueVariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'view'   => Pages\ViewAttributeValue::route('/{record}'),
            'edit'   => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }

    public static function toggleActiveState(AttributeValue $record): void
    {
        // Refresh the record via an unscoped query so we can safely flip the
        // active flag even when the global scopes would normally hide it.
        $unscopedRecord = AttributeValue::withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ])->findOrFail($record->getKey());

        $newActiveState = ! $unscopedRecord->is_active;

        $unscopedRecord->forceFill([
            'is_active' => $newActiveState,
        ])->save();

        // Mirror the in-memory instance so callers immediately see the
        // updated state without waiting for a manual refresh.
        $record->is_active = $newActiveState;

        Notification::make()
            ->title($newActiveState
                ? __('attribute_values.activated_successfully')
                : __('attribute_values.deactivated_successfully'))
            ->success()
            ->send();
    }

    public static function setAsDefault(AttributeValue $record): void
    {
        // Clear the default flag on sibling records without the storefront
        // scopes so previously inactive defaults are also reset.
        AttributeValue::withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ])
            ->where('attribute_id', $record->attribute_id)
            ->whereKeyNot($record->getKey())
            ->update([
                'is_default' => false,
            ]);

        AttributeValue::withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ])
            ->whereKey($record->getKey())
            ->update([
                'is_default' => true,
            ]);

        // Keep the current instance in sync with the persisted value so
        // assertions and UI refreshes see the toggle immediately.
        $record->is_default = true;

        Notification::make()
            ->title(__('attribute_values.set_as_default_successfully'))
            ->success()
            ->send();
    }

    public static function duplicateAttributeValue(AttributeValue $record, bool $notify = true): AttributeValue
    {
        $duplicate = AttributeValue::withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ])
            ->findOrFail($record->getKey())
            ->replicate([
                'products_count',
                'variants_count',
            ]);

        $duplicate->value = $record->value . ' (Copy)';
        $duplicate->slug = Str::slug($duplicate->value . '-' . Str::random(6));
        $duplicate->is_default = false;
        $duplicate->save();

        if ($notify) {
            Notification::make()
                ->title(__('attribute_values.duplicated_successfully'))
                ->success()
                ->send();
        }

        return $duplicate;
    }

    public static function activateRecords(Collection $records): void
    {
        // Operate on a fresh query so even currently hidden records receive the
        // activation flag update in bulk.
        AttributeValue::withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ])
            ->whereIn('id', $records->modelKeys())
            ->update([
                'is_active' => true,
            ]);

        // Sync in-memory models so Filament bulk actions don't require a full
        // table refresh to display the new state.
        $records->each(function (AttributeValue $value): void {
            $value->is_active = true;
        });

        Notification::make()
            ->title(__('attribute_values.bulk_activated_success'))
            ->success()
            ->send();
    }
}
