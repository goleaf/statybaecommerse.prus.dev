<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use App\Models\Zone;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * AddressResource
 *
 * Filament v4 resource for Address management in the admin panel.
 */
final class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    /**
     * Get navigation label
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.addresses');
    }

    /**
     * Get navigation group
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Orders';
    }

    /**
     * Get model label
     */
    public static function getModelLabel(): string
    {
        return __('admin.models.address');
    }

    /**
     * Get plural model label
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.models.addresses');
    }

    /**
     * Configure the Filament form schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('translations.address_information'))
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('user_id')
                            ->label(__('translations.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('type')
                            ->label(__('translations.type'))
                            ->options(AddressType::options())
                            ->required()
                            ->default(AddressType::SHIPPING),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('first_name')
                            ->label(__('translations.first_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('translations.last_name'))
                            ->required()
                            ->maxLength(255),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('company_name')
                            ->label(__('translations.company'))
                            ->maxLength(255),
                        TextInput::make('company_vat')
                            ->label(__('translations.company_vat'))
                            ->maxLength(50),
                    ]),
                ]),
            Section::make(__('translations.address_details'))
                ->schema([
                    TextInput::make('address_line_1')
                        ->label(__('translations.address_line_1'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('address_line_2')
                        ->label(__('translations.address_line_2'))
                        ->maxLength(255),
                    Grid::make(3)->schema([
                        TextInput::make('apartment')
                            ->label(__('translations.apartment'))
                            ->maxLength(100),
                        TextInput::make('floor')
                            ->label(__('translations.floor'))
                            ->maxLength(100),
                        TextInput::make('building')
                            ->label(__('translations.building'))
                            ->maxLength(100),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('city')
                            ->label(__('translations.city'))
                            ->required()
                            ->maxLength(100),
                        TextInput::make('state')
                            ->label(__('translations.state'))
                            ->maxLength(100),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('postal_code')
                            ->label(__('translations.postal_code'))
                            ->required()
                            ->maxLength(20),
                        TextInput::make('country_code')
                            ->label(__('translations.country_code'))
                            ->required()
                            ->maxLength(2)
                            ->default('LT'),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('country_id')
                            ->label(__('translations.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('zone_id')
                            ->label(__('translations.zone'))
                            ->relationship('zone', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                ]),
            Section::make(__('translations.contact_information'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('phone')
                            ->label(__('translations.phone'))
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label(__('translations.email'))
                            ->email()
                            ->maxLength(255),
                    ]),
                    TextInput::make('landmark')
                        ->label(__('translations.landmark'))
                        ->maxLength(255),
                ]),
            Section::make(__('translations.additional_information'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('translations.notes'))
                        ->maxLength(1000)
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('instructions')
                        ->label(__('translations.instructions'))
                        ->maxLength(1000)
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('translations.settings'))
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('is_default')
                            ->label(__('translations.is_default'))
                            ->helperText(__('translations.is_default_help')),
                        Toggle::make('is_active')
                            ->label(__('translations.is_active'))
                            ->default(true)
                            ->helperText(__('translations.is_active_help')),
                    ]),
                    Grid::make(2)->schema([
                        Toggle::make('is_billing')
                            ->label(__('translations.is_billing'))
                            ->helperText(__('translations.is_billing_help')),
                        Toggle::make('is_shipping')
                            ->label(__('translations.is_shipping'))
                            ->helperText(__('translations.is_shipping_help')),
                    ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('translations.user'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('display_name')
                    ->label(__('translations.full_name'))
                    ->sortable()
                    ->searchable(['first_name', 'last_name', 'company_name']),
                TextColumn::make('type')
                    ->label(__('translations.type'))
                    ->formatStateUsing(fn($state) => $state->label())
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        AddressType::SHIPPING => 'primary',
                        AddressType::BILLING => 'success',
                        AddressType::HOME => 'warning',
                        AddressType::WORK => 'info',
                        AddressType::OTHER => 'secondary',
                        default => 'gray',
                    }),
                TextColumn::make('full_address')
                    ->label(__('translations.address'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('city')
                    ->label(__('translations.city'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('translations.country'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('translations.phone'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('is_default')
                    ->label(__('translations.is_default'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? __('translations.yes') : __('translations.no'))
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('is_billing')
                    ->label(__('translations.is_billing'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? __('translations.yes') : __('translations.no'))
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('is_shipping')
                    ->label(__('translations.is_shipping'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? __('translations.yes') : __('translations.no'))
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->label(__('translations.is_active'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? __('translations.yes') : __('translations.no'))
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('translations.type'))
                    ->options(AddressType::options()),
                SelectFilter::make('country_id')
                    ->label(__('translations.country'))
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('translations.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_default')
                    ->label(__('translations.is_default')),
                TernaryFilter::make('is_billing')
                    ->label(__('translations.is_billing')),
                TernaryFilter::make('is_shipping')
                    ->label(__('translations.is_shipping')),
                TernaryFilter::make('is_active')
                    ->label(__('translations.is_active')),
                Filter::make('has_company')
                    ->label(__('translations.has_company'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('company_name')),
                Filter::make('created_this_month')
                    ->label(__('translations.created_this_month'))
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
            ])
            ->actions([
                Action::make('set_default')
                    ->label(__('translations.set_as_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function (Address $record) {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(__('translations.address_set_as_default'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Address $record) => !$record->is_default),
                Action::make('duplicate')
                    ->label(__('translations.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Address $record) {
                        $newAddress = $record->replicate();
                        $newAddress->is_default = false;
                        $newAddress->save();

                        Notification::make()
                            ->title(__('translations.address_duplicated'))
                            ->success()
                            ->send();
                    }),
                Action::make('toggle_active')
                    ->label(fn(Address $record) => $record->is_active ? __('translations.deactivate') : __('translations.activate'))
                    ->icon(fn(Address $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(Address $record) => $record->is_active ? 'danger' : 'success')
                    ->action(function (Address $record) {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('translations.address_activated') : __('translations.address_deactivated'))
                            ->success()
                            ->send();
                    }),
                ViewAction::make()
                    ->color('info'),
                EditAction::make()
                    ->color('warning'),
                DeleteAction::make()
                    ->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('translations.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('translations.addresses_activated'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('translations.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('translations.addresses_deactivated'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('set_billing')
                        ->label(__('translations.set_as_billing'))
                        ->icon('heroicon-o-credit-card')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_billing' => true]);
                            Notification::make()
                                ->title(__('translations.addresses_set_as_billing'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('set_shipping')
                        ->label(__('translations.set_as_shipping'))
                        ->icon('heroicon-o-truck')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_shipping' => true]);
                            Notification::make()
                                ->title(__('translations.addresses_set_as_shipping'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('export')
                        ->label(__('translations.export'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (Collection $records) {
                            // Export logic would go here
                            Notification::make()
                                ->title(__('translations.addresses_exported'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')  // Auto-refresh every 30 seconds
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->reorderable('sort_order')
            ->searchable()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistFiltersInSession();
    }

    /**
     * Get relations
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Get pages
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'view' => Pages\ViewAddress::route('/{record}'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }

    /**
     * Get navigation badge
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();
        $activeCount = static::getModel()::where('is_active', true)->count();

        if ($activeCount === 0) {
            return null;
        }

        return $activeCount === $count ? (string) $count : "{$activeCount}/{$count}";
    }

    /**
     * Get navigation badge color
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        $activeCount = static::getModel()::where('is_active', true)->count();

        if ($activeCount === 0) {
            return 'danger';
        }

        if ($activeCount === $count) {
            return 'success';
        }

        return 'warning';
    }

    /**
     * Get global search result title
     */
    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->display_name;
    }

    /**
     * Get global search result details
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('translations.user') => $record->user->name,
            __('translations.type') => $record->type_label,
            __('translations.city') => $record->city,
            __('translations.country') => $record->country?->name,
        ];
    }

    /**
     * Get globally searchable attributes
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'company_name', 'city', 'address_line_1'];
    }
}
