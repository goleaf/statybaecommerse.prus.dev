<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
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
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('locations.basic_information'))
                ->components([
                    Grid::make(2)
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
            Section::make(__('locations.geographic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('country_code')
                                ->label(__('locations.country'))
                                ->relationship('country', 'name')
                                ->searchable()
                                ->preload()
                                ->live(),
                        ]),
                ]),
            Section::make(__('locations.coordinates'))
                ->components([
                    Grid::make(2)
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
            Section::make(__('locations.address_information'))
                ->components([
                    Grid::make(2)
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
            Section::make(__('locations.contact_information'))
                ->components([
                    Grid::make(2)
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
            Section::make(__('locations.fields.opening_hours'))
                ->components([
                    Repeater::make('opening_hours')
                        ->label(__('locations.fields.opening_hours'))
                        ->schema([
                            Select::make('day')
                                ->label(__('locations.fields.day'))
                                ->options([
                                    'monday'    => __('locations.days.monday'),
                                    'tuesday'   => __('locations.days.tuesday'),
                                    'wednesday' => __('locations.days.wednesday'),
                                    'thursday'  => __('locations.days.thursday'),
                                    'friday'    => __('locations.days.friday'),
                                    'saturday'  => __('locations.days.saturday'),
                                    'sunday'    => __('locations.days.sunday'),
                                ])
                                ->required(),
                            Toggle::make('is_closed')
                                ->label(__('locations.fields.is_closed'))
                                ->live(),
                            Flatpickr::makeTime('open_time')
                                ->label(__('locations.fields.open_time'))
                                ->visible(fn ($get) => ! $get('is_closed')),
                            Flatpickr::makeTime('close_time')
                                ->label(__('locations.fields.close_time'))
                                ->visible(fn ($get) => ! $get('is_closed')),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['day'] ?? null),
                ]),
            Section::make(__('locations.details.contact_info'))
                ->components([
                    KeyValue::make('contact_info')
                        ->label(__('locations.fields.contact_info'))
                        ->keyLabel(__('locations.fields.contact_field'))
                        ->valueLabel(__('locations.fields.contact_value'))
                        ->addActionLabel(__('locations.actions.add_contact_field')),
                ]),
            Section::make(__('locations.business_settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_enabled')
                                ->label(__('locations.is_enabled'))
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
                                    'warehouse'           => __('locations.types.warehouse'),
                                    'store'               => __('locations.types.store'),
                                    'office'              => __('locations.types.office'),
                                    'distribution_center' => __('locations.types.distribution_center'),
                                    'pickup_point'        => __('locations.types.pickup_point'),
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
                TextColumn::make('city')
                    ->label(__('locations.city'))
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
                IconColumn::make('is_enabled')
                    ->label(__('locations.is_enabled'))
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
                        'warehouse'           => __('locations.types.warehouse'),
                        'store'               => __('locations.types.store'),
                        'office'              => __('locations.types.office'),
                        'distribution_center' => __('locations.types.distribution_center'),
                        'pickup_point'        => __('locations.types.pickup_point'),
                    ]),
                TernaryFilter::make('is_enabled')
                    ->trueLabel(__('locations.filter_enabled'))
                    ->falseLabel(__('locations.filter_disabled'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->trueLabel(__('locations.filters.default_only'))
                    ->falseLabel(__('locations.filters.non_default_only'))
                    ->native(false),
                SelectFilter::make('has_coordinates')
                    ->label(__('locations.filters.has_coordinates'))
                    ->options([
                        'yes' => __('locations.with_coordinates'),
                        'no'  => __('locations.without_coordinates'),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $value = $data['value'] ?? null;

                        if ($value === 'yes') {
                            $query->whereNotNull('latitude')->whereNotNull('longitude');
                        } elseif ($data['value'] === 'no') {
                            $query->where(function ($q): void {
                                $q->whereNull('latitude')->orWhereNull('longitude');
                            });
                        }
                    }),
                SelectFilter::make('has_opening_hours')
                    ->label(__('locations.filters.has_opening_hours'))
                    ->options([
                        'yes' => __('locations.with_opening_hours'),
                        'no'  => __('locations.without_opening_hours'),
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
                Action::make('toggle_enabled')
                    ->label(fn (Location $record): string => $record->is_enabled ? __('locations.deactivate') : __('locations.activate'))
                    ->icon(fn (Location $record): string => $record->is_enabled ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Location $record): string => $record->is_enabled ? 'warning' : 'success')
                    ->action(function (Location $record): void {
                        $record->update(['is_enabled' => ! $record->is_enabled]);
                        Notification::make()
                            ->title($record->is_enabled ? __('locations.activated_successfully') : __('locations.deactivated_successfully'))
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
                            // Set first selected as default
                            $records->first()->update(['is_default' => true]);
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
