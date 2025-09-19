<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LocationResource\Pages;
use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * LocationResource
 *
 * Filament v4 resource for Location management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    /**
     * @var UnitEnum|string|null
     */
    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('locations.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "System";
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('locations.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('locations.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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
                                ->required()
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
                                ->required()
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
                        ]),
                    Grid::make(2)
                        ->components([
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
                    KeyValue::make('address')
                        ->label(__('locations.address'))
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
            Section::make(__('locations.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('locations.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('locations.is_default')),
                        ]),
                    Grid::make(2)
                        ->components([
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
     * @param Table $table
     * @return Table
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
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('country.name')
                    ->label(__('locations.country'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city.name')
                    ->label(__('locations.city'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label(__('locations.type'))
                    ->formatStateUsing(fn(string $state): string => __("locations.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'warehouse' => 'blue',
                        'store' => 'green',
                        'office' => 'purple',
                        'distribution_center' => 'orange',
                        'pickup_point' => 'pink',
                        default => 'gray',
                    }),
                TextColumn::make('phone')
                    ->label(__('locations.phone'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('locations.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
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
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label(__('locations.country'))
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('city_id')
                    ->label(__('locations.city'))
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('locations.type'))
                    ->options([
                        'warehouse' => __('locations.types.warehouse'),
                        'store' => __('locations.types.store'),
                        'office' => __('locations.types.office'),
                        'distribution_center' => __('locations.types.distribution_center'),
                        'pickup_point' => __('locations.types.pickup_point'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('locations.is_active'))
                    ->boolean()
                    ->trueLabel(__('locations.active_only'))
                    ->falseLabel(__('locations.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->label(__('locations.is_default'))
                    ->boolean()
                    ->trueLabel(__('locations.default_only'))
                    ->falseLabel(__('locations.non_default_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(Location $record): string => $record->is_active ? __('locations.deactivate') : __('locations.activate'))
                    ->icon(fn(Location $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Location $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Location $record): void {
                        $record->update(['is_active' => !$record->is_active]);

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
                    ->visible(fn(Location $record): bool => !$record->is_default)
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
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
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
