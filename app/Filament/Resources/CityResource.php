<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use App\Models\Country;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
 * CityResource
 *
 * Filament v4 resource for City management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CityResource extends Resource
{
    protected static ?string $model = City::class;

    /**
     * @var UnitEnum|string|null
     */
    protected static string|UnitEnum|null $navigationGroup = "Products";

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('cities.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "System"->value;
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('cities.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('cities.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('cities.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('cities.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('cities.code'))
                                ->maxLength(10)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Select::make('country_id')
                        ->label(__('cities.country'))
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
                    Grid::make(2)
                        ->schema([
                            TextInput::make('country_code')
                                ->label(__('cities.country_code'))
                                ->maxLength(3)
                                ->disabled(),
                            TextInput::make('state_province')
                                ->label(__('cities.state_province'))
                        ]),
                    Textarea::make('description')
                        ->label(__('cities.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('cities.coordinates'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('latitude')
                                ->label(__('cities.latitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-90)
                                ->maxValue(90),
                            TextInput::make('longitude')
                                ->label(__('cities.longitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-180)
                                ->maxValue(180),
                        ]),
                ]),
            Section::make(__('cities.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('cities.is_active'))
                                ->default(true),
                            Toggle::make('is_capital')
                                ->label(__('cities.is_capital')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('population')
                                ->label(__('cities.population'))
                                ->numeric()
                                ->minValue(0),
                            TextInput::make('sort_order')
                                ->label(__('cities.sort_order'))
                                ->numeric()
                                ->default(0),
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
                    ->label(__('cities.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('cities.code'))
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('country.name')
                    ->label(__('cities.country'))
                    ->color('blue'),
                TextColumn::make('country_code')
                    ->label(__('cities.country_code'))
                    ->color('green')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('state_province')
                    ->label(__('cities.state_province'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('population')
                    ->label(__('cities.population'))
                    ->numeric()
                    ->formatStateUsing(fn($state): string => $state ? number_format($state) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('cities.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_capital')
                    ->label(__('cities.is_capital'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('cities.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('cities.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('cities.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('cities.active_only'))
                    ->falseLabel(__('cities.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_capital')
                    ->trueLabel(__('cities.capital_only'))
                    ->falseLabel(__('cities.non_capital_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(City $record): string => $record->is_active ? __('cities.deactivate') : __('cities.activate'))
                    ->icon(fn(City $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(City $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (City $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('cities.activated_successfully') : __('cities.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('cities.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('cities.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('cities.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('cities.bulk_deactivated_success'))
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
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'view' => Pages\ViewCity::route('/{record}'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
