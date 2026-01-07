<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerGroupResource\Pages;
use App\Models\CustomerGroup;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;
use UnitEnum;

final class CustomerGroupResource extends Resource
{
    use SpatieTranslatableResource;

    protected static ?string $model = CustomerGroup::class;

    public static function getNavigationGroup(): UnitEnum|string
    {
        return __('customer_groups.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('customer_groups.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('customer_groups.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('customer_groups.code'))
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('customer_groups.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('customer_groups.pricing_settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('discount_percentage')
                                // Surface the legacy percentage field so simple create flows remain backwards compatible.
                                ->label(__('customer_groups.discount_percentage'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->default(0),
                            TextInput::make('discount_fixed')
                                ->label(__('customer_groups.discount_fixed'))
                                ->helperText(__('customer_groups.discount_fixed_help'))
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01)
                                ->default(0)
                                ->rules(['numeric', 'min:0']),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('has_special_pricing')
                                ->label(__('customer_groups.has_special_pricing'))
                                ->default(false),
                            Toggle::make('has_volume_discounts')
                                ->label(__('customer_groups.has_volume_discounts'))
                                ->default(false),
                        ]),
                ]),
            SchemaSection::make(__('customer_groups.permissions'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('can_view_prices')
                                ->label(__('customer_groups.can_view_prices'))
                                ->default(true),
                            Toggle::make('can_place_orders')
                                ->label(__('customer_groups.can_place_orders'))
                                ->default(true),
                            Toggle::make('can_view_catalog')
                                ->label(__('customer_groups.can_view_catalog'))
                                ->default(true),
                            Toggle::make('can_use_coupons')
                                ->label(__('customer_groups.can_use_coupons'))
                                ->default(true),
                        ]),
                ]),
            SchemaSection::make(__('customer_groups.settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('customer_groups.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('customer_groups.is_default'))
                                ->default(false),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('customer_groups.type'))
                                ->options(self::typeOptions())
                                // Default to the legacy "regular" type so the column never fails strict database constraints.
                                ->default('regular')
                                ->required()
                                ->rules([Rule::in(array_keys(self::typeOptions()))]),
                            TextInput::make('sort_order')
                                ->label(__('customer_groups.sort_order'))
                                ->numeric()
                                ->default(0),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('customer_groups.table_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('customer_groups.code'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('customer_groups.is_active'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('customer_groups.is_default'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('customer_groups.is_active'))
                    ->options([
                        '1' => __('customer_groups.enabled_only'),
                        '0' => __('customer_groups.disabled_only'),
                    ])
                    ->query(function (Builder $query, $value): Builder {
                        return $query->when($value !== null, function (Builder $innerQuery) use ($value): Builder {
                            $state = self::resolveBooleanFilterValue($value);

                            if ($state === null) {
                                return $innerQuery->withoutGlobalScopes();
                            }

                            return $innerQuery->withoutGlobalScopes()->where('is_active', $state);
                        });
                    }),
                SelectFilter::make('is_default')
                    ->label(__('customer_groups.is_default'))
                    ->options([
                        '1' => __('customer_groups.default_only'),
                        '0' => __('customer_groups.non_default_only'),
                    ])
                    ->query(function (Builder $query, $value): Builder {
                        $state = self::resolveBooleanFilterValue($value);

                        if ($state === null) {
                            return $query;
                        }

                        return $query->withoutGlobalScopes()->where('is_default', $state);
                    }),
                SelectFilter::make('has_special_pricing')
                    ->label(__('customer_groups.has_special_pricing'))
                    ->options([
                        '1' => __('customer_groups.only_special_pricing'),
                        '0' => __('customer_groups.all_groups'),
                    ])
                    ->query(function (Builder $query, $value): Builder {
                        $state = self::resolveBooleanFilterValue($value);

                        if ($state === null) {
                            return $query;
                        }

                        return $query->withoutGlobalScopes()->where('has_special_pricing', $state);
                    }),
                SelectFilter::make('has_volume_discounts')
                    ->label(__('customer_groups.has_volume_discounts'))
                    ->options([
                        '1' => __('customer_groups.only_volume_discounts'),
                        '0' => __('customer_groups.all_groups'),
                    ])
                    ->query(function (Builder $query, $value): Builder {
                        $state = self::resolveBooleanFilterValue($value);

                        if ($state === null) {
                            return $query;
                        }

                        return $query->withoutGlobalScopes()->where('has_volume_discounts', $state);
                    }),
                SelectFilter::make('type')
                    ->label(__('customer_groups.type'))
                    ->options(self::typeOptions())
                    ->query(fn (Builder $query, $value): Builder => $query->when(
                        filled($value),
                        fn (Builder $q) => $q->where('type', $value)
                    )),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(function (?CustomerGroup $record): string {
                        if ($record?->is_active) {
                            return __('customer_groups.deactivate');
                        }

                        return __('customer_groups.activate');
                    })
                    ->icon(fn (?CustomerGroup $record): string => $record?->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (?CustomerGroup $record): string => $record?->is_active ? 'warning' : 'success')
                    ->visible(fn (?CustomerGroup $record): bool => $record !== null)
                    ->action(function (?CustomerGroup $record): void {
                        if (! $record instanceof CustomerGroup) {
                            return;
                        }

                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('customer_groups.activated_successfully') : __('customer_groups.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('customer_groups.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('primary')
                    ->visible(fn (?CustomerGroup $record): bool => $record?->is_default === false)
                    ->action(function (CustomerGroup $record): void {
                        CustomerGroup::query()
                            ->whereKeyNot($record->getKey())
                            ->update(['is_default' => false]);

                        $record->update([
                            'is_default' => true,
                            'is_active'  => true,
                        ]);

                        Notification::make()
                            ->title(__('customer_groups.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (?CustomerGroup $record): bool => $record instanceof CustomerGroup)
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('customer_groups.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records
                                ->filter(fn (?CustomerGroup $group): bool => $group instanceof CustomerGroup)
                                ->each(static function (CustomerGroup $group): void {
                                    $group->update(['is_active' => true]);
                                });

                            Notification::make()
                                ->title(__('customer_groups.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('customer_groups.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records
                                ->filter(fn (?CustomerGroup $group): bool => $group instanceof CustomerGroup)
                                ->each(static function (CustomerGroup $group): void {
                                    $group->update(['is_active' => false]);
                                });

                            Notification::make()
                                ->title(__('customer_groups.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomerGroups::route('/'),
            'create' => Pages\CreateCustomerGroup::route('/create'),
            'view'   => Pages\ViewCustomerGroup::route('/{record}'),
            'edit'   => Pages\EditCustomerGroup::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function typeOptions(): array
    {
        return [
            'regular'   => __('customer_groups.type_regular'),
            'vip'       => __('customer_groups.type_vip'),
            'wholesale' => __('customer_groups.type_wholesale'),
            'retail'    => __('customer_groups.type_retail'),
            'corporate' => __('customer_groups.type_corporate'),
        ];
    }

    private static function resolveBooleanFilterValue(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($filtered !== null) {
            return $filtered;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $normalized = strtolower($value);

            if (in_array($normalized, ['yes', 'true', '1', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['no', 'false', '0', 'off'], true)) {
                return false;
            }
        }

        return null;
    }

    public static function mutateLocalizedAttributes(array $data): array
    {
        $normalized = $data;

        foreach (['name', 'description'] as $attribute) {
            if (! array_key_exists($attribute, $normalized)) {
                continue;
            }

            $value = $normalized[$attribute];

            if (is_array($value)) {
                $normalized[$attribute] = json_encode($value, JSON_THROW_ON_ERROR);
            }
        }

        return $normalized;
    }
}
