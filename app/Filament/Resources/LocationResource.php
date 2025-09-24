<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
    protected static ?string $model = Location::class;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('locations.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('locations.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('locations.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('locations.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('locations.code'))
                                ->maxLength(10)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('locations.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('locations.geographic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('country_id')
                                ->label(__('locations.country'))
                                ->relationship('country', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $country = Country::find($state);
                                        if ($country) {
                                            $set('country_code', $country->code);
                                        }
                                    }
                                }),
                            Select::make('city_id')
                                ->label(__('locations.city'))
                                ->relationship('city', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $city = City::find($state);
                                        if ($city) {
                                            $set('city_code', $city->code);
                                        }
                                    }
                                }),
                            TextInput::make('country_code')
                                ->label(__('locations.country_code'))
                                ->maxLength(3)
                                ->disabled(),
                            TextInput::make('city_code')
                                ->label(__('locations.city_code'))
                                ->maxLength(10)
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('locations.coordinates'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('latitude')
                                ->label(__('locations.latitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-90)
                                ->maxValue(90),
                            TextInput::make('longitude')
                                ->label(__('locations.longitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-180)
                                ->maxValue(180),
                        ]),
                ]),
            Section::make(__('locations.address_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('address_line_1')
                                ->label(__('locations.address_line_1'))
                                ->maxLength(255),
                            TextInput::make('address_line_2')
                                ->label(__('locations.address_line_2'))
                                ->maxLength(255),
                            TextInput::make('city')
                                ->label(__('locations.city'))
                                ->maxLength(100),
                            TextInput::make('state')
                                ->label(__('locations.state'))
                                ->maxLength(100),
                            TextInput::make('postal_code')
                                ->label(__('locations.postal_code'))
                                ->maxLength(20),
                        ]),
                    KeyValue::make('address')
                        ->label(__('locations.additional_address'))
                        ->keyLabel(__('locations.address_field'))
                        ->valueLabel(__('locations.address_value'))
                        ->addActionLabel(__('locations.add_address_field')),
                ]),
            Section::make(__('locations.contact_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('phone')
                                ->label(__('locations.phone'))
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('email')
                                ->label(__('locations.email'))
                                ->email()
                                ->maxLength(255),
                        ]),
                    TextInput::make('website')
                        ->label(__('locations.website'))
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
            Section::make(__('locations.opening_hours'))
                ->components([
                    Repeater::make('opening_hours')
                        ->label(__('locations.opening_hours'))
                        ->schema([
                            Select::make('day')
                                ->label(__('locations.day'))
                                ->options([
                                    'monday' => __('locations.days.monday'),
                                    'tuesday' => __('locations.days.tuesday'),
                                    'wednesday' => __('locations.days.wednesday'),
                                    'thursday' => __('locations.days.thursday'),
                                    'friday' => __('locations.days.friday'),
                                    'saturday' => __('locations.days.saturday'),
                                    'sunday' => __('locations.days.sunday'),
                                ])
                                ->required(),
                            Toggle::make('is_closed')
                                ->label(__('locations.is_closed'))
                                ->live(),
                            TimePicker::make('open_time')
                                ->label(__('locations.open_time'))
                                ->visible(fn ($get) => ! $get('is_closed')),
                            TimePicker::make('close_time')
                                ->label(__('locations.close_time'))
                                ->visible(fn ($get) => ! $get('is_closed')),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['day'] ?? null),
                ]),
            Section::make(__('locations.contact_info'))
                ->components([
                    KeyValue::make('contact_info')
                        ->label(__('locations.contact_info'))
                        ->keyLabel(__('locations.contact_field'))
                        ->valueLabel(__('locations.contact_value'))
                        ->addActionLabel(__('locations.add_contact_field')),
                ]),
            Section::make(__('locations.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('locations.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('locations.is_default')),
                            TextInput::make('sort_order')
                                ->label(__('locations.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Select::make('type')
                                ->label(__('locations.type'))
                                ->options([
                                    'warehouse' => __('locations.types.warehouse'),
                                    'store' => __('locations.types.store'),
                                    'office' => __('locations.types.office'),
                                    'distribution_center' => __('locations.types.distribution_center'),
                                    'pickup_point' => __('locations.types.pickup_point'),
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
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('locations.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('locations.code'))
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('country.name')
                    ->label(__('locations.country'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city.name')
                    ->label(__('locations.city'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label(__('locations.type'))
                    ->formatStateUsing(fn (string $state): string => __("locations.types.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'warehouse' => 'blue',
                        'store' => 'green',
                        'office' => 'purple',
                        'distribution_center' => 'orange',
                        'pickup_point' => 'pink',
                        default => 'gray',
                    }),
                TextColumn::make('phone')
                    ->label(__('locations.phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('locations.email'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('locations.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('locations.is_default'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('locations.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('locations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('locations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('inventories_count')
                    ->label(__('locations.inventories_count'))
                    ->counts('inventories')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('variant_inventories_count')
                    ->label(__('locations.variant_inventories_count'))
                    ->counts('variantInventories')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->preload(),
                SelectFilter::make('city_id')
                    ->relationship('city', 'name')
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        'warehouse' => __('locations.types.warehouse'),
                        'store' => __('locations.types.store'),
                        'office' => __('locations.types.office'),
                        'distribution_center' => __('locations.types.distribution_center'),
                        'pickup_point' => __('locations.types.pickup_point'),
                    ]),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('locations.active_only'))
                    ->falseLabel(__('locations.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->trueLabel(__('locations.default_only'))
                    ->falseLabel(__('locations.non_default_only'))
                    ->native(false),
                SelectFilter::make('has_coordinates')
                    ->label(__('locations.has_coordinates'))
                    ->options([
                        'yes' => __('locations.with_coordinates'),
                        'no' => __('locations.without_coordinates'),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['value'] === 'yes') {
                            $query->whereNotNull('latitude')->whereNotNull('longitude');
                        } elseif ($data['value'] === 'no') {
                            $query->where(function ($q) {
                                $q->whereNull('latitude')->orWhereNull('longitude');
                            });
                        }
                    }),
                SelectFilter::make('has_opening_hours')
                    ->label(__('locations.has_opening_hours'))
                    ->options([
                        'yes' => __('locations.with_opening_hours'),
                        'no' => __('locations.without_opening_hours'),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['value'] === 'yes') {
                            $query->whereNotNull('opening_hours');
                        } elseif ($data['value'] === 'no') {
                            $query->whereNull('opening_hours');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Location $record): string => $record->is_active ? __('locations.deactivate') : __('locations.activate'))
                    ->icon(fn (Location $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Location $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Location $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('locations.activated_successfully') : __('locations.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('locations.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Location $record): bool => ! $record->is_default)
                    ->action(function (Location $record): void {
                        // Remove default from other locations
                        Location::where('is_default', true)->update(['is_default' => false]);
                        // Set this location as default
                        $record->update(['is_default' => true]);
                        Notification::make()
                            ->title(__('locations.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('view_on_map')
                    ->label(__('locations.view_on_map'))
                    ->icon('heroicon-o-map')
                    ->color('info')
                    ->url(fn (Location $record): string => $record->google_maps_url ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn (Location $record): bool => $record->hasCoordinates()),
                Action::make('copy_coordinates')
                    ->label(__('locations.copy_coordinates'))
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->action(function (Location $record): void {
                        $coordinates = $record->coordinates;
                        if ($coordinates) {
                            Notification::make()
                                ->title(__('locations.coordinates_copied'))
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
                        ->label(__('locations.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('locations.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('locations.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('locations.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('set_default')
                        ->label(__('locations.set_as_default_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Remove default from other locations
                            Location::where('is_default', true)->update(['is_default' => false]);
                            // Set first selected as default
                            $records->first()->update(['is_default' => true]);
                            Notification::make()
                                ->title(__('locations.bulk_set_default_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('export_coordinates')
                        ->label(__('locations.export_coordinates'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $coordinates = $records
                                ->filter(fn ($record) => $record->hasCoordinates())
                                ->map(fn ($record) => [
                                    'name' => $record->name,
                                    'latitude' => $record->latitude,
                                    'longitude' => $record->longitude,
                                    'address' => $record->full_address,
                                ])
                                ->toArray();

                            Notification::make()
                                ->title(__('locations.coordinates_exported'))
                                ->body(__('locations.coordinates_count', ['count' => count($coordinates)]))
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'view' => Pages\ViewLocation::route('/{record}'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
