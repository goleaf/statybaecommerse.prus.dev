<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Country;
use App\Models\Location;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\TimePicker;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final /**
 * LocationResource
 * 
 * Filament resource for admin panel management.
 */
class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('locations.navigation_label');
    }

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'E-commerce';

    public static function getModelLabel(): string
    {
        return __('locations.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('locations.plural_model_label');
    }

    public static function form(Schema $schema): Schema {
        return $schema->schema([
                Section::make(__('locations.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('locations.code'))
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->helperText(__('locations.code_help')),
                                TextInput::make('name')
                                    ->label(__('locations.name'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Textarea::make('description')
                            ->label(__('locations.description'))
                            ->maxLength(1000)
                            ->rows(3),
                        Select::make('type')
                            ->label(__('locations.type'))
                            ->options([
                                'warehouse' => __('locations.type_warehouse'),
                                'store' => __('locations.type_store'),
                                'office' => __('locations.type_office'),
                                'pickup_point' => __('locations.type_pickup_point'),
                                'other' => __('locations.type_other'),
                            ])
                            ->default('warehouse')
                            ->required(),
                    ])
                    ->columns(1),

                Section::make(__('locations.address_information'))
                    ->schema([
                        TextInput::make('address_line_1')
                            ->label(__('locations.address_line_1'))
                            ->maxLength(500),
                        TextInput::make('address_line_2')
                            ->label(__('locations.address_line_2'))
                            ->maxLength(500),
                        Grid::make(3)
                            ->schema([
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
                        Grid::make(2)
                            ->schema([
                                Select::make('country_code')
                                    ->label(__('locations.country_code'))
                                    ->options(Country::all()->pluck('name', 'cca2'))
                                    ->searchable()
                                    ->maxLength(2),
                                TextInput::make('phone')
                                    ->label(__('locations.phone'))
                                    ->tel()
                                    ->maxLength(20),
                            ]),
                        TextInput::make('email')
                            ->label(__('locations.email'))
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make(__('locations.location_details'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label(__('locations.latitude'))
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->minValue(-90)
                                    ->maxValue(90),
                                TextInput::make('longitude')
                                    ->label(__('locations.longitude'))
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->minValue(-180)
                                    ->maxValue(180),
                            ]),
                        Repeater::make('opening_hours')
                            ->label(__('locations.opening_hours'))
                            ->schema([
                                Select::make('day')
                                    ->label(__('locations.day'))
                                    ->options([
                                        'monday' => __('locations.monday'),
                                        'tuesday' => __('locations.tuesday'),
                                        'wednesday' => __('locations.wednesday'),
                                        'thursday' => __('locations.thursday'),
                                        'friday' => __('locations.friday'),
                                        'saturday' => __('locations.saturday'),
                                        'sunday' => __('locations.sunday'),
                                    ])
                                    ->required(),
                                TimePicker::make('open_time')
                                    ->label(__('locations.open_time')),
                                TimePicker::make('close_time')
                                    ->label(__('locations.close_time')),
                                Toggle::make('is_closed')
                                    ->label(__('locations.is_closed')),
                            ])
                            ->columns(4)
                            ->defaultItems(7),
                        KeyValue::make('contact_info')
                            ->label(__('locations.contact_info'))
                            ->keyLabel(__('locations.contact_type'))
                            ->valueLabel(__('locations.contact_value')),
                    ])
                    ->columns(1),

                Section::make(__('locations.settings'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('locations.is_enabled'))
                                    ->default(true),
                                Toggle::make('is_default')
                                    ->label(__('locations.is_default'))
                                    ->default(false),
                                TextInput::make('sort_order')
                                    ->label(__('locations.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('locations.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('locations.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('locations.type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'warehouse' => __('locations.type_warehouse'),
                        'store' => __('locations.type_store'),
                        'office' => __('locations.type_office'),
                        'pickup_point' => __('locations.type_pickup_point'),
                        'other' => __('locations.type_other'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'warehouse' => 'info',
                        'store' => 'success',
                        'office' => 'warning',
                        'pickup_point' => 'primary',
                        'other' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('full_address')
                    ->label(__('locations.address'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('locations.city'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->label(__('locations.country_code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('locations.phone'))
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('locations.enabled'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('locations.default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('locations.sort_order'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('locations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('locations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_enabled')
                    ->label(__('locations.filter_enabled')),
                SelectFilter::make('type')
                    ->label(__('locations.filter_type'))
                    ->options([
                        'warehouse' => __('locations.type_warehouse'),
                        'store' => __('locations.type_store'),
                        'office' => __('locations.type_office'),
                        'pickup_point' => __('locations.type_pickup_point'),
                        'other' => __('locations.type_other'),
                    ]),
                SelectFilter::make('country_code')
                    ->label(__('locations.filter_country'))
                    ->options(Country::all()->pluck('name', 'cca2'))
                    ->searchable(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_enabled')
                    ->label(__('locations.toggle_enabled'))
                    ->icon('heroicon-o-power')
                    ->action(function (Location $record) {
                        $record->update(['is_enabled' => ! $record->is_enabled]);
                    })
                    ->requiresConfirmation()
                    ->color(fn (Location $record) => $record->is_enabled ? 'danger' : 'success'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('enable')
                        ->label(__('locations.bulk_enable'))
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => true]))
                        ->requiresConfirmation()
                        ->color('success'),
                    BulkAction::make('disable')
                        ->label(__('locations.bulk_disable'))
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => false]))
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoriesRelationManager::class,
            RelationManagers\VariantInventoriesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\LocationStatsWidget::class,
            \App\Filament\Widgets\LocationChartWidget::class,
            \App\Filament\Widgets\LocationInventoryWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'view' => Pages\ViewLocation::route('/{record}'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['country']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'city', 'address_line_1'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('locations.type') => match ($record->type) {
                'warehouse' => __('locations.type_warehouse'),
                'store' => __('locations.type_store'),
                'office' => __('locations.type_office'),
                'pickup_point' => __('locations.type_pickup_point'),
                'other' => __('locations.type_other'),
                default => $record->type,
            },
            __('locations.city') => $record->city,
            __('locations.country_code') => $record->country_code,
        ];
    }
}
