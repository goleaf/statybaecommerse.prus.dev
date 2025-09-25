<?php

declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantAttributeValueResource\Pages;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * VariantAttributeValueResource
 *
 * Filament v4 resource for VariantAttributeValue management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantAttributeValueResource extends Resource
{
    protected static ?string $model = VariantAttributeValue::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 18;

    public static function getNavigationLabel(): string
    {
        return __('admin.variant_attribute_values.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.variant_attribute_values.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.variant_attribute_values.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.variant_attribute_values.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('variant_id')
                                ->label(__('admin.variant_attribute_values.variant'))
                                ->relationship('variant', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $variant = ProductVariant::find($state);
                                        if ($variant) {
                                            $set('variant_name', $variant->name);
                                        }
                                    }
                                }),
                            TextInput::make('variant_name')
                                ->label(__('admin.variant_attribute_values.variant_name'))
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('attribute_id')
                                ->label(__('admin.variant_attribute_values.attribute'))
                                ->relationship('attribute', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $attribute = Attribute::find($state);
                                        if ($attribute) {
                                            $set('attribute_name', $attribute->name);
                                        }
                                    }
                                }),
                            TextInput::make('attribute_name')
                                ->label(__('admin.variant_attribute_values.attribute_name'))
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                ]),
            Section::make(__('admin.variant_attribute_values.attribute_values'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('attribute_value')
                                ->label(__('admin.variant_attribute_values.attribute_value'))
                                ->required()
                                ->maxLength(255)
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $set('attribute_value_slug', \Str::slug($state));
                                    }
                                }),
                            TextInput::make('attribute_value_display')
                                ->label(__('admin.variant_attribute_values.attribute_value_display'))
                                ->maxLength(255)
                                ->helperText(__('admin.variant_attribute_values.attribute_value_display_help')),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('attribute_value_lt')
                                ->label(__('admin.variant_attribute_values.attribute_value_lt'))
                                ->maxLength(255)
                                ->helperText(__('admin.variant_attribute_values.lithuanian_value')),
                            TextInput::make('attribute_value_en')
                                ->label(__('admin.variant_attribute_values.attribute_value_en'))
                                ->maxLength(255)
                                ->helperText(__('admin.variant_attribute_values.english_value')),
                            TextInput::make('attribute_value_slug')
                                ->label(__('admin.variant_attribute_values.attribute_value_slug'))
                                ->maxLength(255)
                                ->helperText(__('admin.variant_attribute_values.slug_help')),
                        ]),
                ]),
            Section::make(__('admin.variant_attribute_values.settings'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('admin.variant_attribute_values.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(9999),
                            Toggle::make('is_filterable')
                                ->label(__('admin.variant_attribute_values.is_filterable'))
                                ->default(true)
                                ->helperText(__('admin.variant_attribute_values.is_filterable_help')),
                            Toggle::make('is_searchable')
                                ->label(__('admin.variant_attribute_values.is_searchable'))
                                ->default(true)
                                ->helperText(__('admin.variant_attribute_values.is_searchable_help')),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.name')
                    ->label(__('admin.variant_attribute_values.variant'))
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('attribute.name')
                    ->label(__('admin.variant_attribute_values.attribute'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('attribute_name')
                    ->label(__('admin.variant_attribute_values.attribute_name'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('attribute_value')
                    ->label(__('admin.variant_attribute_values.attribute_value'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->limit(50),
                TextColumn::make('attribute_value_display')
                    ->label(__('admin.variant_attribute_values.attribute_value_display'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),
                TextColumn::make('attribute_value_lt')
                    ->label(__('admin.variant_attribute_values.attribute_value_lt'))
                    ->toggleable()
                    ->limit(30)
                    ->color('success'),
                TextColumn::make('attribute_value_en')
                    ->label(__('admin.variant_attribute_values.attribute_value_en'))
                    ->toggleable()
                    ->limit(30)
                    ->color('warning'),
                TextColumn::make('sort_order')
                    ->label(__('admin.variant_attribute_values.sort_order'))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_filterable')
                    ->label(__('admin.variant_attribute_values.is_filterable'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_searchable')
                    ->label(__('admin.variant_attribute_values.is_searchable'))
                    ->boolean()
                    ->trueIcon('heroicon-o-magnifying-glass')
                    ->falseIcon('heroicon-o-magnifying-glass-minus')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('admin.variant_attribute_values.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
                TextColumn::make('updated_at')
                    ->label(__('admin.variant_attribute_values.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('variant_id')
                    ->label(__('admin.variant_attribute_values.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('attribute_id')
                    ->label(__('admin.variant_attribute_values.attribute'))
                    ->relationship('attribute', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('has_translations')
                    ->label(__('admin.variant_attribute_values.has_translations'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('attribute_value_lt')->orWhereNotNull('attribute_value_en')),
                Filter::make('missing_translations')
                    ->label(__('admin.variant_attribute_values.missing_translations'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('attribute_value_lt')->orWhereNull('attribute_value_en')),
                TernaryFilter::make('is_filterable')
                    ->label(__('admin.variant_attribute_values.is_filterable'))
                    ->trueLabel(__('admin.variant_attribute_values.filterable_only'))
                    ->falseLabel(__('admin.variant_attribute_values.not_filterable_only'))
                    ->native(false),
                TernaryFilter::make('is_searchable')
                    ->label(__('admin.variant_attribute_values.is_searchable'))
                    ->trueLabel(__('admin.variant_attribute_values.searchable_only'))
                    ->falseLabel(__('admin.variant_attribute_values.not_searchable_only'))
                    ->native(false),
            ])
            ->groups([
                Group::make('variant.name')
                    ->label(__('admin.variant_attribute_values.group_by_variant'))
                    ->collapsible(),
                Group::make('attribute.name')
                    ->label(__('admin.variant_attribute_values.group_by_attribute'))
                    ->collapsible(),
                Group::make('is_filterable')
                    ->label(__('admin.variant_attribute_values.group_by_filterable'))
                    ->collapsible(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_filterable')
                    ->label(fn (VariantAttributeValue $record): string => $record->is_filterable ? __('admin.variant_attribute_values.make_not_filterable') : __('admin.variant_attribute_values.make_filterable'))
                    ->icon(fn (VariantAttributeValue $record): string => $record->is_filterable ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (VariantAttributeValue $record): string => $record->is_filterable ? 'warning' : 'success')
                    ->action(function (VariantAttributeValue $record): void {
                        $record->update(['is_filterable' => ! $record->is_filterable]);
                        Notification::make()
                            ->title($record->is_filterable ? __('admin.variant_attribute_values.made_filterable_successfully') : __('admin.variant_attribute_values.made_not_filterable_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('toggle_searchable')
                    ->label(fn (VariantAttributeValue $record): string => $record->is_searchable ? __('admin.variant_attribute_values.make_not_searchable') : __('admin.variant_attribute_values.make_searchable'))
                    ->icon(fn (VariantAttributeValue $record): string => $record->is_searchable ? 'heroicon-o-magnifying-glass-minus' : 'heroicon-o-magnifying-glass')
                    ->color(fn (VariantAttributeValue $record): string => $record->is_searchable ? 'warning' : 'success')
                    ->action(function (VariantAttributeValue $record): void {
                        $record->update(['is_searchable' => ! $record->is_searchable]);
                        Notification::make()
                            ->title($record->is_searchable ? __('admin.variant_attribute_values.made_searchable_successfully') : __('admin.variant_attribute_values.made_not_searchable_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('duplicate')
                    ->label(__('admin.variant_attribute_values.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (VariantAttributeValue $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->attribute_value = $record->attribute_value.' (Copy)';
                        $newRecord->save();
                        Notification::make()
                            ->title(__('admin.variant_attribute_values.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('make_filterable')
                        ->label(__('admin.variant_attribute_values.make_filterable'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_filterable' => true]);
                            Notification::make()
                                ->title(__('admin.variant_attribute_values.made_filterable_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('make_not_filterable')
                        ->label(__('admin.variant_attribute_values.make_not_filterable'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_filterable' => false]);
                            Notification::make()
                                ->title(__('admin.variant_attribute_values.made_not_filterable_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('make_searchable')
                        ->label(__('admin.variant_attribute_values.make_searchable'))
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_searchable' => true]);
                            Notification::make()
                                ->title(__('admin.variant_attribute_values.made_searchable_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('make_not_searchable')
                        ->label(__('admin.variant_attribute_values.make_not_searchable'))
                        ->icon('heroicon-o-magnifying-glass-minus')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_searchable' => false]);
                            Notification::make()
                                ->title(__('admin.variant_attribute_values.made_not_searchable_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('update_sort_order')
                        ->label(__('admin.variant_attribute_values.update_sort_order'))
                        ->icon('heroicon-o-arrows-up-down')
                        ->color('info')
                        ->form([
                            TextInput::make('sort_order')
                                ->label(__('admin.variant_attribute_values.sort_order'))
                                ->numeric()
                                ->required()
                                ->default(0),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['sort_order' => $data['sort_order']]);
                            Notification::make()
                                ->title(__('admin.variant_attribute_values.sort_order_updated_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListVariantAttributeValues::route('/'),
            'create' => Pages\CreateVariantAttributeValue::route('/create'),
            'view' => Pages\ViewVariantAttributeValue::route('/{record}'),
            'edit' => Pages\EditVariantAttributeValue::route('/{record}/edit'),
        ];
    }
}
