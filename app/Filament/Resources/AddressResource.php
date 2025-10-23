<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Enums\AddressType;
use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\AddressSearch;
use App\Support\Search\CustomerSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;
use Filament\Schemas\Schema;

/**
 * AddressResource
 *
 * Filament v4 resource for Address management in the admin panel.
 * Provides comprehensive CRUD operations with advanced filtering, relations, and multilingual support.
 */
final class AddressResource extends Resource
{
    use SpatieTranslatableResource; // Enable locale-aware management for Spatie translatable attributes.

    protected static ?string $model = Address::class;

    protected static ?int $navigationSort = 3;

    /**
     * @var string|\BackedEnum|null Explicit navigation icon keeps the Address menu visually distinct.
     */
    protected static $navigationIcon = 'heroicon-o-map-pin';

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
    public static function getNavigationGroup(): string|UnitEnum|null
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
     * Configure the Filament schema using the Filament v4 Schema API.
     */
    public static function form(Schema $schema): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return $schema->schema([
            Section::make(__('translations.address_information'))
                ->components([
                    Grid::make(2)->components([
                        SearchableInput::make('user_id')
                            ->label(__('translations.user'))
                            ->placeholder('Name, email or phone')
                            ->required()
                            ->searchUsing(fn (string $search): array => CustomerSearch::byEmailPhoneName($search))
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                            ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?Address $record): void {
                                // Hydrate via the shared helper so the metadata lifecycle matches the documentation.
                                SearchableInputHelper::hydrate(
                                    $component,
                                    $state,
                                    static function (int $value) use ($record): ?array {
                                        $user = $record?->user;

                                        if (! $user instanceof User || $user->getKey() !== $value) {
                                            $user = User::query()
                                                ->select(['id', 'name', 'email'])
                                                ->find($value);
                                        }

                                        if (! $user instanceof User) {
                                            return null;
                                        }

                                        $label = trim(sprintf('%s <%s>', (string) ($user->name ?? ''), (string) ($user->email ?? '')));

                                        return [
                                            'value' => $user->getKey(),
                                            'label' => $label,
                                        ];
                                    },
                                );
                            })
                            ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                                if ($state === null || $state === '') {
                                    // Reset the stored relation id when the lookup clears.
                                    SearchableInputHelper::clear($component, $set, ['user_id' => null]);

                                    return;
                                }

                                $set('user_id', (int) $state);
                            }),
                        Select::make('type')
                            ->label(__('translations.type'))
                            ->options(AddressType::options())
                            ->required()
                            ->default(AddressType::SHIPPING->value),
                    ]),
                    Grid::make(2)->components([
                        TextInput::make('first_name')
                            ->label(__('translations.first_name'))
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('translations.last_name'))
                            ->maxLength(255),
                    ]),
                    Grid::make(2)->components([
                        TextInput::make('company_name')
                            ->label(__('translations.company'))
                            ->maxLength(255),
                        TextInput::make('company_vat')
                            ->label(__('translations.company_vat'))
                            ->maxLength(50),
                    ]),
                ]),
            Section::make(__('translations.address_details'))
                ->components([
                    SearchableInput::make('address_line_1')
                        ->label(__('translations.address_line_1'))
                        ->placeholder(__('translations.address_line_1'))
                        ->required()
                        ->maxLength(255)
                        ->searchUsing(fn (string $term): array => AddressSearch::labels($term)),
                    TextInput::make('address_line_2')
                        ->label(__('translations.address_line_2'))
                        ->maxLength(255),
                    Grid::make(3)->components([
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
                    Grid::make(3)->components([
                        SearchableInput::make('city')
                            ->label(__('translations.city'))
                            ->placeholder(__('translations.city'))
                            ->required()
                            ->maxLength(100)
                            ->searchUsing(fn (string $term): array => AddressSearch::cities($term)),
                        TextInput::make('state')
                            ->label(__('translations.state'))
                            ->maxLength(100),
                        TextInput::make('postal_code')
                            ->label(__('translations.postal_code'))
                            ->required()
                            ->maxLength(20),
                    ]),
                    Grid::make(2)->components([
                        Select::make('country_code')
                            ->label(__('translations.country'))
                            ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'cca2')->all())
                            ->searchable()
                            ->default('LT')
                            ->required(fn (string $context): bool => $context === 'create'),
                        SearchableInput::make('city_id')
                            ->label(__('translations.city_id'))
                            ->placeholder(__('translations.city'))
                            ->searchUsing(fn (string $term): array => AddressSearch::cityResults($term))
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                            ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?Address $record): void {
                                // Hydrate via helper so the metadata lifecycle mirrors docs/forms/SEARCHABLE_INPUT_METADATA.md.
                                SearchableInputHelper::hydrate(
                                    $component,
                                    $state,
                                    static function (int $value) use ($record): ?array {
                                        $city = $record?->cityById ?? City::query()
                                            ->select(['id', 'name', 'country_code'])
                                            ->find($value);

                                        if (! $city instanceof City) {
                                            return null;
                                        }

                                        return [
                                            'value' => $city->getKey(),
                                            'label' => (string) ($city->name ?? ''),
                                        ];
                                    },
                                );
                            })
                            ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                                if ($state === null || $state === '') {
                                    // Clear cached identifiers when the lookup resets.
                                    SearchableInputHelper::clear($component, $set, [
                                        'city_id' => null,
                                    ]);

                                    return;
                                }

                                $city = City::query()
                                    ->select(['id', 'name', 'country_code'])
                                    ->find((int) $state);

                                if (! $city instanceof City) {
                                    return;
                                }

                                $set('city_id', $city->getKey());

                                $name = $city->getAttribute('name');
                                if (is_string($name)) {
                                    $set('city', $name);
                                }

                                $country = $city->getAttribute('country_code');
                                if (is_string($country)) {
                                    $set('country_code', $country);
                                }
                            })
                            ->dehydrated(false),
                    ]),
                ]),
            Section::make(__('translations.contact_information'))
                ->components([
                    Grid::make(2)->components([
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
                ->components([
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
                ->components([
                    Grid::make(2)->components([
                        Toggle::make('is_default')
                            ->label(__('translations.is_default'))
                            ->helperText(__('translations.is_default_help')),
                        Toggle::make('is_active')
                            ->label(__('translations.is_active'))
                            ->default(true)
                            ->helperText(__('translations.is_active_help')),
                    ]),
                    Grid::make(2)->components([
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
     * Configure the Filament table with comprehensive columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            // Returning the configured Table instance ensures bulk actions remain type-checked by Filament.
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
                    ->searchable(['first_name', 'last_name', 'company_name']),
                TextColumn::make('type')
                    ->label(__('translations.type'))
                    ->formatStateUsing(function ($state): string {
                        $enumState = $state instanceof AddressType
                            ? $state
                            : AddressType::tryFrom((string) $state);

                        return $enumState?->label() ?? (string) $state;
                    })
                    ->badge()
                    ->color(function ($state): string {
                        $enumState = $state instanceof AddressType
                            ? $state
                            : AddressType::tryFrom((string) $state);

                        return match ($enumState) {
                            AddressType::SHIPPING => 'primary',
                            AddressType::BILLING  => 'success',
                            AddressType::HOME     => 'warning',
                            AddressType::WORK     => 'info',
                            AddressType::OTHER    => 'secondary',
                            default               => 'gray',
                        };
                    }),
                TextColumn::make('full_address')
                    ->label(__('translations.address'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

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
                IconColumn::make('is_default')
                    ->label(__('translations.is_default'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_billing')
                    ->label(__('translations.is_billing'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_shipping')
                    ->label(__('translations.is_shipping'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('translations.is_active'))
                    ->boolean()
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
                    ->options(AddressType::options()),
                SelectFilter::make('country_code')
                    ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'cca2')->all()),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
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
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('company_name')),
                Filter::make('created_this_month')
                    ->label(__('translations.created_this_month'))
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('first_name'),
                        TextConstraint::make('last_name'),
                        TextConstraint::make('company_name'),
                        TextConstraint::make('city'),
                        TextConstraint::make('postal_code'),
                        NumberConstraint::make('id'),
                        DateConstraint::make('created_at'),
                        DateConstraint::make('updated_at'),
                    ]),
            ])
            ->actions([
                Action::make('set_default')
                    ->label(__('translations.set_as_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function (Address $record): void {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(__('translations.address_set_as_default'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Address $record) => ! $record->is_default),
                Action::make('duplicate')
                    ->label(__('translations.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Address $record): void {
                        $newAddress = $record->replicate();
                        $newAddress->is_default = false;
                        $newAddress->save();
                        Notification::make()
                            ->title(__('translations.address_duplicated'))
                            ->success()
                            ->send();
                    }),
                Action::make('toggle_active')
                    ->label(fn (Address $record) => $record->is_active ? __('translations.deactivate') : __('translations.activate'))
                    ->icon(fn (Address $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Address $record) => $record->is_active ? 'danger' : 'success')
                    ->action(function (Address $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
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
                        ->action(function (Collection $records): void {
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
                        ->action(function (Collection $records): void {
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
                        ->action(function (Collection $records): void {
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
                        ->action(function (Collection $records): void {
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
                        ->action(function (Collection $records): void {
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
            ->paginationPageOptions([10, 25, 50, 100])
            ->reorderable('sort_order')
            ->searchable()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistFiltersInSession();
    }

    /**
     * Get relations for this resource
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get pages for this resource
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'view'   => Pages\ViewAddress::route('/{record}'),
            'edit'   => Pages\EditAddress::route('/{record}/edit'),
        ];
    }

    /**
     * Get navigation badge
     */
    public static function getNavigationBadge(): ?string
    {
        $count = self::getModel()::count();
        $activeCount = self::getModel()::where('is_active', true)->count();
        if ($activeCount === 0) {
            return null;
        }

        return $activeCount === $count ? (string) $count : "{$activeCount}/{$count}";
    }

    /**
     * Get navigation badge color
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = self::getModel()::count();
        $activeCount = self::getModel()::where('is_active', true)->count();
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
            __('translations.user')    => $record->user->name,
            __('translations.type')    => $record->type_label,
            __('translations.city')    => $record->city,
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