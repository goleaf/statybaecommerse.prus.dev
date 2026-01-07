<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use App\Support\Concerns\HasNav;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class LocationResource extends Resource
{
    use HasNav;

    protected static ?string $model = Location::class;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('locations.plural_model_label');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('locations.model_label');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('locations.basic_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('locations.fields.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('locations.fields.code'))
                                ->maxLength(10)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('locations.fields.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('locations.geographic_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            Select::make('country_code')
                                ->label(__('locations.fields.country_code'))
                                ->options(fn () => Country::withoutGlobalScopes()->orderBy('name')->pluck('name', 'cca2')->toArray())
                                ->searchable()
                                ->preload()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Forms\Set $set, Forms\Get $get): void {
                                    if (! $state) {
                                        $set('country_id', null);
                                        $set('city_id', null);
                                        $set('city_code', null);

                                        return;
                                    }

                                    $country = Country::withoutGlobalScopes()->where('cca2', $state)->first();

                                    if ($country) {
                                        $set('country_id', $country->getKey());
                                    }

                                    $currentCityId = $get('city_id');

                                    if ($currentCityId) {
                                        $city = City::withoutGlobalScopes()->find($currentCityId);

                                        if (! $city || ($city->country && ($city->country->cca2 ?? $city->country->code) !== $state)) {
                                            $set('city_id', null);
                                            $set('city_code', null);
                                        } else {
                                            $set('city_code', $city->code);
                                        }
                                    } else {
                                        $set('city_id', null);
                                        $set('city_code', null);
                                    }
                                })
                                ->afterStateHydrated(function (?string $state, Forms\Set $set, Forms\Get $get): void {
                                    if (! $state) {
                                        $set('country_id', null);

                                        return;
                                    }

                                    $country = Country::withoutGlobalScopes()->where('cca2', $state)->first();
                                    if ($country) {
                                        $set('country_id', $country->getKey());
                                    }

                                    $currentCityId = $get('city_id');
                                    if ($currentCityId) {
                                        $city = City::withoutGlobalScopes()->find($currentCityId);
                                        if (! $city) {
                                            $set('city_id', null);
                                            $set('city_code', null);

                                            return;
                                        }

                                        if ($city->country) {
                                            $cityCountryCode = $city->country->cca2 ?? $city->country->code;

                                            if ($cityCountryCode !== $state) {
                                                $set('country_code', $cityCountryCode);
                                                $set('country_id', $city->country->getKey());
                                            }
                                        }

                                        $set('city_code', $city->code);
                                    }
                                }),
                            Select::make('city_id')
                                ->label(__('locations.fields.city'))
                                ->relationship('city', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                    if (! $state) {
                                        $set('city_code', null);

                                        return;
                                    }

                                    $city = City::withoutGlobalScopes()->find($state);
                                    if ($city === null) {
                                        $set('city_code', null);

                                        return;
                                    }

                                    $set('city_code', $city->code);

                                    if ($city->country) {
                                        $set('country_code', $city->country->cca2 ?? $city->country->code);
                                        $set('country_id', $city->country->getKey());
                                    }
                                })
                                ->afterStateHydrated(function ($state, Forms\Set $set): void {
                                    if (! $state) {
                                        $set('city_code', null);

                                        return;
                                    }

                                    $city = City::withoutGlobalScopes()->find($state);
                                    if ($city) {
                                        $set('city_code', $city->code);

                                        if ($city->country) {
                                            $set('country_code', $city->country->cca2 ?? $city->country->code);
                                            $set('country_id', $city->country->getKey());
                                        }
                                    }
                                }),
                            Hidden::make('country_id'),
                            TextInput::make('city_code')
                                ->label(__('locations.fields.city_code'))
                                ->maxLength(10)
                                ->disabled(),
                        ]),
                ]),
            SchemaSection::make(__('locations.coordinates'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            TextInput::make('latitude')
                                ->label(__('locations.fields.latitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-90)
                                ->maxValue(90),
                            TextInput::make('longitude')
                                ->label(__('locations.fields.longitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-180)
                                ->maxValue(180),
                        ]),
                ]),
            SchemaSection::make(__('locations.address_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            TextInput::make('address_line_1')
                                ->label(__('locations.fields.address_line_1'))
                                ->maxLength(255),
                            TextInput::make('address_line_2')
                                ->label(__('locations.fields.address_line_2'))
                                ->maxLength(255),
                            TextInput::make('city')
                                ->label(__('locations.fields.city'))
                                ->maxLength(100),
                            TextInput::make('state')
                                ->label(__('locations.fields.state'))
                                ->maxLength(100),
                            TextInput::make('postal_code')
                                ->label(__('locations.fields.postal_code'))
                                ->maxLength(20),
                        ]),
                    KeyValue::make('address')
                        ->label(__('locations.fields.additional_address'))
                        ->keyLabel(__('locations.fields.address_field'))
                        ->valueLabel(__('locations.fields.address_value'))
                        ->addActionLabel(__('locations.actions.add_address_field')),
                ]),
            SchemaSection::make(__('locations.contact_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            TextInput::make('phone')
                                ->label(__('locations.fields.phone'))
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('email')
                                ->label(__('locations.fields.email'))
                                ->email()
                                ->maxLength(255),
                        ]),
                    TextInput::make('website')
                        ->label(__('locations.fields.website'))
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('locations.fields.opening_hours'))
                ->components([
                    Repeater::make('opening_hours')
                        ->label(__('locations.fields.opening_hours'))
                        ->schema([
                            Select::make('day')
                                ->label(__('locations.fields.day'))
                                ->options([
                                    'monday'    => __('locations.monday'),
                                    'tuesday'   => __('locations.tuesday'),
                                    'wednesday' => __('locations.wednesday'),
                                    'thursday'  => __('locations.thursday'),
                                    'friday'    => __('locations.friday'),
                                    'saturday'  => __('locations.saturday'),
                                    'sunday'    => __('locations.sunday'),
                                ])
                                ->required(),
                            Toggle::make('is_closed')
                                ->label(__('locations.fields.is_closed'))
                                ->live(),
                            SupportFlatpickr::makeTime('open_time')
                                ->label(__('locations.fields.open_time'))
                                ->visible(fn ($get) => ! $get('is_closed')),
                            SupportFlatpickr::makeTime('close_time')
                                ->label(__('locations.fields.close_time'))
                                ->visible(fn ($get) => ! $get('is_closed')),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['day'] ?? null),
                ]),
            SchemaSection::make(__('locations.details.contact_info'))
                ->components([
                    KeyValue::make('contact_info')
                        ->label(__('locations.fields.contact_info'))
                        ->keyLabel(__('locations.fields.contact_field'))
                        ->valueLabel(__('locations.fields.contact_value'))
                        ->addActionLabel(__('locations.actions.add_contact_field')),
                ]),
            SchemaSection::make(__('locations.business_settings'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('locations.fields.is_enabled'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('locations.fields.is_default')),
                            TextInput::make('sort_order')
                                ->label(__('locations.fields.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Select::make('type')
                                ->label(__('locations.fields.type'))
                                ->options([
                                    'warehouse'           => __('locations.type_warehouse'),
                                    'store'               => __('locations.type_store'),
                                    'office'              => __('locations.type_office'),
                                    'distribution_center' => __('locations.type_distribution_center'),
                                    'pickup_point'        => __('locations.type_pickup_point'),
                                ])
                                ->default('warehouse'),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('locations.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('locations.fields.code'))
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('country.name')
                    ->label(__('locations.fields.country'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city.name')
                    ->label(__('locations.fields.city'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label(__('locations.fields.type'))
                    ->formatStateUsing(fn (string $state): string => __('locations.type_' . $state))
                    ->color(fn (string $state): string => match ($state) {
                        'warehouse'           => 'blue',
                        'store'               => 'green',
                        'office'              => 'purple',
                        'distribution_center' => 'orange',
                        'pickup_point'        => 'pink',
                        default               => 'gray',
                    }),
                TextColumn::make('phone')
                    ->label(__('locations.fields.phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('locations.fields.email'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('locations.fields.is_enabled'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('locations.fields.is_default'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('locations.fields.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('locations.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('locations.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('inventories_count')
                    ->label(__('locations.fields.inventories_count'))
                    ->counts('inventories')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('variant_inventories_count')
                    ->label(__('locations.fields.variant_inventories_count'))
                    ->counts('variantInventories')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_code')
                    ->relationship('country', 'name')
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        'warehouse'           => __('locations.type_warehouse'),
                        'store'               => __('locations.type_store'),
                        'office'              => __('locations.type_office'),
                        'distribution_center' => __('locations.type_distribution_center'),
                        'pickup_point'        => __('locations.type_pickup_point'),
                    ]),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('locations.filters.active_only'))
                    ->falseLabel(__('locations.filters.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->trueLabel(__('locations.filters.default_only'))
                    ->falseLabel(__('locations.filters.non_default_only'))
                    ->native(false),
                SelectFilter::make('has_coordinates')
                    ->label(__('locations.filters.has_coordinates'))
                    ->options([
                        'yes' => __('locations.filters.with_coordinates'),
                        'no'  => __('locations.filters.without_coordinates'),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $value = $data['value'] ?? null;

                        if ($value === 'yes') {
                            $query->whereNotNull('latitude')->whereNotNull('longitude');
                        } elseif ($value === 'no') {
                            $query->where(function ($q): void {
                                $q->whereNull('latitude')->orWhereNull('longitude');
                            });
                        }
                    }),
                SelectFilter::make('has_opening_hours')
                    ->label(__('locations.filters.has_opening_hours'))
                    ->options([
                        'yes' => __('locations.filters.with_opening_hours'),
                        'no'  => __('locations.filters.without_opening_hours'),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $value = $data['value'] ?? null;

                        if ($value === 'yes') {
                            $query->whereNotNull('opening_hours');
                        } elseif ($value === 'no') {
                            $query->whereNull('opening_hours');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Location $record): string => $record->is_active ? __('locations.actions.deactivate') : __('locations.actions.activate'))
                    ->icon(fn (Location $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Location $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Location $record): void {
                        $record->update(['is_enabled' => ! $record->is_enabled]);
                        Notification::make()
                            ->title($record->is_active ? __('locations.messages.activated') : __('locations.messages.deactivated'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('locations.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Location $record): bool => ! $record->is_default)
                    ->action(function (Location $record): void {
                        // Remove default from other locations
                        Location::where('is_default', true)->update(['is_default' => false]);
                        // Set this location as default
                        $record->update(['is_default' => true]);
                        Notification::make()
                            ->title(__('locations.messages.set_as_default_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('view_on_map')
                    ->label(__('locations.actions.show_on_map'))
                    ->icon('heroicon-o-map')
                    ->color('info')
                    ->url(fn (Location $record): string => $record->google_maps_url ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn (Location $record): bool => $record->hasCoordinates()),
                Action::make('copy_coordinates')
                    ->label(__('locations.actions.copy_coordinates'))
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->action(function (Location $record): void {
                        $coordinates = $record->coordinates;
                        if ($coordinates) {
                            Notification::make()
                                ->title(__('locations.messages.coordinates_copied'))
                                ->body($coordinates)
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(fn (Location $record): bool => $record->hasCoordinates()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('locations.actions.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_enabled' => true]);
                            Notification::make()
                                ->title(__('locations.messages.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('locations.actions.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_enabled' => false]);
                            Notification::make()
                                ->title(__('locations.messages.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('set_default')
                        ->label(__('locations.actions.set_default_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Remove default from other locations
                            Location::where('is_default', true)->update(['is_default' => false]);
                            // Set first selected as default (null-safe)
                            $records->first()?->update(['is_default' => true]);
                            Notification::make()
                                ->title(__('locations.messages.bulk_set_default_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('export_coordinates')
                        ->label(__('locations.actions.export_coordinates'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $coordinates = $records
                                ->filter(fn ($record) => $record->hasCoordinates())
                                ->map(fn ($record) => [
                                    'name'      => $record->name,
                                    'latitude'  => $record->latitude,
                                    'longitude' => $record->longitude,
                                    'address'   => $record->full_address,
                                ])
                                ->toArray();

                            Notification::make()
                                ->title(__('locations.messages.coordinates_exported'))
                                ->body(__('locations.messages.coordinates_count', ['count' => count($coordinates)]))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'view'   => Pages\ViewLocation::route('/{record}'),
            'edit'   => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
