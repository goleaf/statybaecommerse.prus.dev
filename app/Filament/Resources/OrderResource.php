<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use App\Services\Export\Contracts\DefinesExportColumns;
use App\Services\Export\ExportColumn;
use App\Services\Export\ExportFormat;
use App\Services\Export\ExportService;
use App\Services\Pricing\PriceCalculator;
use App\Support\Authorization\AuthorizationMatrix;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\Filters\DateRangeFilter;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\AddressSearch;
use App\Support\Search\ChannelSearch;
use App\Support\Search\CustomerSearch;
use App\Support\Search\PartnerSearch;
use App\Support\Seo\LocaleUrlGenerator;
use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use BackedEnum;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Tapp\FilamentValueRangeFilter\Filters\ValueRangeFilter;

/**
 * OrderResource
 *
 * Comprehensive Filament v4 resource for Order management with advanced features:
 * - Multi-language support with translations
 * - Advanced filtering and search capabilities
 * - Bulk operations and custom actions
 * - Real-time status updates
 * - Comprehensive form validation
 * - Export capabilities
 * - Audit trail integration
 */
final class OrderResource extends Resource implements DefinesExportColumns
{
    use SpatieTranslatableResource; // Enable locale-aware management for Spatie translatable attributes.

    protected static ?string $model = Order::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $navigationLabel = 'orders.navigation.orders';

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', Order::class);
    }

    public static function canView(Model $record): bool
    {
        return $record instanceof Order && Gate::allows('view', $record);
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create', Order::class);
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof Order && Gate::allows('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return $record instanceof Order && Gate::allows('delete', $record);
    }

    protected static ?string $modelLabel = 'orders.models.order';

    protected static ?string $pluralModelLabel = 'orders.models.orders';

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('orders', 'viewAny');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('orders', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('orders', 'update');
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-shopping-bag';
    }

    /**
     * Get the navigation label with translation support.
     */
    public static function getNavigationLabel(): string
    {
        return __('orders.navigation.orders');
    }

    /**
     * Get the plural model label with translation support.
     */
    public static function getPluralModelLabel(): string
    {
        return __('orders.models.orders');
    }

    /**
     * Get the model label with translation support.
     */
    public static function getModelLabel(): string
    {
        return __('orders.models.order');
    }

    /**
     * Configure the comprehensive form schema with advanced features.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('orders.sections.order_details'))
                ->description(__('orders.sections.customer_information'))
                ->icon('heroicon-o-information-circle')
                ->schema([
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('number')
                                ->label(__('orders.fields.order_number'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->helperText(__('orders.number_help')),
                            SearchableInput::make('user_id')
                                ->label(__('orders.fields.customer'))
                                ->placeholder('Name, email or phone')
                                ->required()
                                ->searchUsing(fn (string $search): array => CustomerSearch::byEmailPhoneName($search))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                // Refer to docs/forms/SEARCHABLE_INPUT_METADATA.md for helper guidance.
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?Order $record): void {
                                    // Hydrate via helper to keep metadata lifecycle consistent.
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
                                        // Reset relation when lookup clears.
                                        SearchableInputHelper::clear($component, $set, ['user_id' => null]);

                                        return;
                                    }

                                    $set('user_id', (int) $state);
                                }),
                            Select::make('status')
                                ->label(__('orders.fields.status'))
                                ->options(function (): array {
                                    // Keep the selectable lifecycle states synchronized with the enum-backed
                                    // order workflow so partners never see stale string literals.
                                    return OrderStatus::options();
                                })
                                ->default(OrderStatus::PENDING->value),
                        ]),
                    SchemaGrid::make(3)
                        ->schema([
                            Select::make('payment_status')
                                ->label(__('orders.fields.payment_status'))
                                ->options(function (): array {
                                    // Surface the richer payment enum values so reconciliation teams can target
                                    // authorized, captured, and partially refunded states from the same picker.
                                    return self::paymentStatusOptions();
                                }),
                            Select::make('payment_method')
                                ->label(__('orders.fields.payment_method'))
                                ->options([
                                    'credit_card'      => __('orders.payment_methods.credit_card'),
                                    'bank_transfer'    => __('orders.payment_methods.bank_transfer'),
                                    'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                                    'paypal'           => __('orders.payment_methods.paypal'),
                                    'stripe'           => __('orders.payment_methods.stripe'),
                                    'apple_pay'        => __('orders.payment_methods.apple_pay'), // Ensure the metadata surfaces the proper Apple Pay label.
                                    'google_pay'       => __('orders.payment_methods.google_pay'), // Keep Google Pay aligned with the localized payment labels.
                                ]),
                            TextInput::make('payment_reference')
                                ->label(__('orders.fields.payment_reference')) // Present the correct payment reference metadata for operators.
                                ->helperText(__('orders.fields.payment_reference_help') ?? ''),
                        ]),
                ])
                ->collapsible(),
            SchemaSection::make(__('orders.sections.order_details'))
                ->description(__('orders.fields.total'))
                ->icon('heroicon-o-currency-euro')
                ->schema([
                    SchemaGrid::make(4)
                        ->schema([
                            TextInput::make('subtotal')
                                ->label(__('orders.fields.subtotal'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01),
                            TextInput::make('tax_amount')
                                ->label(__('orders.fields.tax_amount'))
                                ->numeric()
                                ->default(0)
                                ->prefix('€')
                                ->step(0.01),
                            TextInput::make('shipping_amount')
                                ->label(__('orders.fields.shipping_amount'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01),
                            TextInput::make('discount_amount')
                                ->label(__('orders.fields.discount_amount'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01),
                        ]),
                    SearchableInput::make('coupon_id')
                        ->label(__('orders.fields.coupon'))
                        ->placeholder(__('orders.fields.coupon_placeholder'))
                        ->searchUsing(fn (string $search): array => CouponSearch::byCode($search))
                        ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                        ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?Order $record): void {
                            if ($state === null) {
                                return;
                            }

                            $coupon = $record?->coupon ?? Coupon::query()->select(['id', 'code', 'name'])->find($state);

                            if (! $coupon instanceof Coupon) {
                                return;
                            }

                            $code = (string) ($coupon->getAttribute('code') ?? '');
                            $name = (string) ($coupon->getAttribute('name') ?? '');
                            $label = trim(sprintf('%s — %s', $code, $name));

                            $component
                                ->state((string) $state)
                                ->options([
                                    (string) $coupon->getKey() => $label,
                                ]);
                        })
                        ->onItemSelected(function (SearchResult $item): void {
                            app()->call(function (Set $set) use ($item): void {
                                $rawId = $item->get('coupon_id');

                                if (! is_numeric($rawId)) {
                                    $rawId = $item->value();
                                }

                                if (! is_numeric($rawId)) {
                                    return;
                                }

                                $set('coupon_id', (int) $rawId);
                            });
                        })
                        ->suffixIcon('heroicon-o-ticket'),
                    Placeholder::make('total')
                        ->label(__('orders.fields.total'))
                        ->content(function (Get $get): string {
                            $subtotal = (float) $get('subtotal') ?? 0;
                            $tax = (float) $get('tax_amount') ?? 0;
                            $shipping = (float) $get('shipping_amount') ?? 0;
                            $discount = (float) $get('discount_amount') ?? 0;
                            $taxable = max(0.0, $subtotal - $discount);
                            $rate = $taxable > 0 ? $tax / $taxable : null;
                            $breakdown = app(PriceCalculator::class)->breakdown($subtotal, $discount, $shipping, $rate);

                            return $breakdown->toSummary()['formatted_total'];
                        }),
                    Hidden::make('total')
                        ->default(function (Get $get): float {
                            $subtotal = (float) $get('subtotal') ?? 0;
                            $tax = (float) $get('tax_amount') ?? 0;
                            $shipping = (float) $get('shipping_amount') ?? 0;
                            $discount = (float) $get('discount_amount') ?? 0;
                            $taxable = max(0.0, $subtotal - $discount);
                            $rate = $taxable > 0 ? $tax / $taxable : null;
                            $breakdown = app(PriceCalculator::class)->breakdown($subtotal, $discount, $shipping, $rate);

                            return $breakdown->total; // Persist the calculated total to keep invoices and exports in sync.
                        }),
                ])
                ->collapsible(),
            SchemaSection::make(__('orders.sections.billing_information'))
                ->description(__('orders.sections.shipping_information'))
                ->icon('heroicon-o-map-pin')
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            SearchableInput::make('billing_address_lookup')
                                ->label(__('orders.lookups.billing_address'))
                                ->placeholder(__('orders.lookups.address_placeholder'))
                                ->searchUsing(fn (string $value): array => AddressSearch::results($value))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                    // Hydrate inline using shared helper to keep metadata lifecycle aligned with docs.
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static function (int $value): ?array {
                                            $address = Address::query()
                                                ->select(['id', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code'])
                                                ->find($value);

                                            if (! $address instanceof Address) {
                                                return null;
                                            }

                                            return [
                                                'value'   => $address->getKey(),
                                                'label'   => self::formatAddress($address),
                                                'payload' => AddressSearch::payload($address),
                                            ];
                                        },
                                    );
                                })
                                // See docs/forms/SEARCHABLE_INPUT_METADATA.md for SearchResult metadata conventions.
                                ->afterStateUpdated(function (SearchableInput $component, ?int $state, Set $set): void {
                                    if ($state === null) {
                                        // Reset the cached billing payload when cleared.
                                        SearchableInputHelper::clear($component, $set, ['billing_address' => []]);

                                        return;
                                    }

                                    $address = Address::query()
                                        ->select(['id', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code'])
                                        ->find($state);

                                    if (! $address instanceof Address) {
                                        SearchableInputHelper::clear($component, $set, ['billing_address' => []]);

                                        return;
                                    }

                                    $set('billing_address', AddressSearch::payload($address));
                                })
                                ->dehydrated(false),
                            SearchableInput::make('shipping_address_lookup')
                                ->label(__('orders.lookups.shipping_address'))
                                ->placeholder(__('orders.lookups.address_placeholder'))
                                ->searchUsing(fn (string $value): array => AddressSearch::results($value))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                    // Hydrate via helper for metadata parity across lookups.
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static function (int $value): ?array {
                                            $address = Address::query()
                                                ->select(['id', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code'])
                                                ->find($value);

                                            if (! $address instanceof Address) {
                                                return null;
                                            }

                                            return [
                                                'value'   => $address->getKey(),
                                                'label'   => self::formatAddress($address),
                                                'payload' => AddressSearch::payload($address),
                                            ];
                                        },
                                    );
                                })
                                // See docs/forms/SEARCHABLE_INPUT_METADATA.md for SearchResult metadata conventions.
                                ->afterStateUpdated(function (SearchableInput $component, ?int $state, Set $set): void {
                                    if ($state === null) {
                                        SearchableInputHelper::clear($component, $set, ['shipping_address' => []]);

                                        return;
                                    }

                                    $address = Address::query()
                                        ->select(['id', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code'])
                                        ->find($state);

                                    if (! $address instanceof Address) {
                                        SearchableInputHelper::clear($component, $set, ['shipping_address' => []]);

                                        return;
                                    }

                                    $set('shipping_address', AddressSearch::payload($address));
                                })
                                ->dehydrated(false),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            KeyValue::make('billing_address')
                                ->label(__('orders.fields.billing_address'))
                                ->keyLabel(__('orders.lookups.address_field'))
                                ->valueLabel(__('orders.lookups.address_value'))
                                ->addActionLabel(__('orders.actions.create'))
                                ->default([]),
                            KeyValue::make('shipping_address')
                                ->label(__('orders.fields.shipping_address'))
                                ->keyLabel(__('orders.lookups.address_field'))
                                ->valueLabel(__('orders.lookups.address_value'))
                                ->addActionLabel(__('orders.actions.create'))
                                ->default([]),
                        ]),
                ])
                ->collapsible(),
            SchemaSection::make(__('orders.sections.order_shipping'))
                ->description(__('orders.sections.shipping_information'))
                ->icon('heroicon-o-truck')
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            SupportFlatpickr::makeDateTime('shipped_at')
                                ->label(__('orders.fields.shipped_at')),
                            SupportFlatpickr::makeDateTime('delivered_at')
                                ->label(__('orders.fields.delivered_at')),
                        ]),
                    TextInput::make('tracking_number')
                        ->label(__('orders.fields.tracking_number'))
                        ->maxLength(255),
                ])
                ->collapsible(),
            SchemaSection::make(__('orders.sections.order_details'))
                ->description(__('orders.fields.notes'))
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('notes')
                        ->label(__('orders.fields.notes'))
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText(__('orders.fields.internal_notes')),
                    SchemaGrid::make(3)
                        ->schema([
                            SearchableInput::make('channel_id')
                                ->label(__('orders.fields.channel'))
                                ->placeholder(__('orders.lookups.channel_placeholder'))
                                ->searchUsing(fn (string $value): array => ChannelSearch::results($value))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                // Helper guidance documented in docs/forms/SEARCHABLE_INPUT_METADATA.md.
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static function (int $value): ?array {
                                            $channel = Channel::query()
                                                ->select(['id', 'name', 'code', 'type'])
                                                ->find($value);

                                            if (! $channel instanceof Channel) {
                                                return null;
                                            }

                                            return [
                                                'value' => $channel->getKey(),
                                                'label' => ChannelSearch::label($channel),
                                            ];
                                        },
                                    );
                                })
                                ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                                    if ($state === null || $state === '') {
                                        SearchableInputHelper::clear($component, $set, ['channel_id' => null]);

                                        return;
                                    }

                                    $set('channel_id', (int) $state);
                                }),
                            SearchableInput::make('partner_id')
                                ->label(__('orders.fields.partner'))
                                ->placeholder(__('orders.lookups.partner_placeholder'))
                                ->searchUsing(fn (string $value): array => PartnerSearch::results($value))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static function (int $value): ?array {
                                            $partner = Partner::query()
                                                ->select(['id', 'name', 'code', 'contact_email'])
                                                ->find($value);

                                            if (! $partner instanceof Partner) {
                                                return null;
                                            }

                                            return [
                                                'value' => $partner->getKey(),
                                                'label' => PartnerSearch::label($partner),
                                            ];
                                        },
                                    );
                                })
                                ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                                    if ($state === null || $state === '') {
                                        SearchableInputHelper::clear($component, $set, ['partner_id' => null]);

                                        return;
                                    }

                                    $set('partner_id', (int) $state);
                                }),
                        ]),
                ])
                ->collapsible(),
        ]);
    }

    /**
     * Compose a consistent address label for SearchableInput helper integrations.
     */
    private static function formatAddress(Address $address): string
    {
        $line1 = (string) ($address->getAttribute('address_line_1') ?? '');
        $line2 = (string) ($address->getAttribute('address_line_2') ?? '');
        $city = (string) ($address->getAttribute('city') ?? '');
        $state = (string) ($address->getAttribute('state') ?? '');
        $postal = (string) ($address->getAttribute('postal_code') ?? '');
        $country = (string) ($address->getAttribute('country_code') ?? '');

        $parts = array_filter([
            $line1,
            $line2,
            $city,
            $state,
            $postal,
            $country !== '' ? strtoupper($country) : '',
        ], static fn (string $value): bool => $value !== '');

        return implode(', ', $parts);
    }

    /**
     * Configure the comprehensive table with advanced features.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                BadgeableColumn::make('number')
                    ->label(__('orders.fields.order_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                ViewColumn::make('quick_links')
                    ->label(__('Quick links'))
                    ->view('filament.tables.columns.list-group')
                    ->state(function (Order $record): array {
                        $localeUrlGenerator = app(LocaleUrlGenerator::class);
                        $locales = collect($localeUrlGenerator->supportedLocales());

                        $items = $locales
                            ->map(function (string $locale) use ($record, $localeUrlGenerator): ?array {
                                $url = $localeUrlGenerator->localizedRoute(
                                    'localized.orders.show',
                                    ['order' => $record->number],
                                    $locale,
                                );

                                if (! $url && Route::has('frontend.orders.show')) {
                                    $url = route('frontend.orders.show', $record);
                                }

                                if (! $url) {
                                    return null;
                                }

                                return [
                                    'label' => __('Order (:locale)', ['locale' => strtoupper($locale)]),
                                    'url'   => $url,
                                    'icon'  => 'heroicon-o-arrow-top-right-on-square',
                                    'color' => 'primary',
                                ];
                            })
                            ->filter()
                            ->values();

                        if (Route::has('api.orders.show')) {
                            $items->push([
                                'label' => __('Order API (:number)', ['number' => $record->number]),
                                'url'   => route('api.orders.show', ['order' => $record->number]),
                                'icon'  => 'heroicon-o-code-bracket',
                                'color' => 'info',
                            ]);
                        }

                        return $items->all();
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label(__('orders.fields.customer'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),
                BadgeableColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->badge()
                    ->color(fn ($state): string => self::resolveOrderStatusMeta($state)['color'])
                    ->icon(fn ($state): string => self::resolveOrderStatusMeta($state)['icon'])
                    ->formatStateUsing(function ($state): string {
                        // Render the translated label generated by the enum so newly introduced states
                        // such as "returned" automatically appear without manual string updates.
                        return self::resolveOrderStatusMeta($state)['label'];
                    })
                    ->sortable()
                    ->asPills()
                    ->searchable(['status', 'payment_status', 'channel.name', 'payment_method'])
                    ->prefixBadges(function (Order $record): array {
                        // Combine payment and channel metadata directly ahead of the main status label.
                        $paymentMeta = self::resolvePaymentStatusMeta($record->payment_status);

                        $badges = [
                            Badge::make('payment_status')
                                ->label(__('orders.badges.payment', ['status' => $paymentMeta['label']]))
                                ->color($paymentMeta['color']),
                        ];

                        if ($record->payment_method) {
                            $method = $record->payment_method instanceof BackedEnum ? $record->payment_method->value : (string) $record->payment_method;
                            $badges[] = Badge::make('payment_method')
                                ->label(__('orders.badges.payment_method', ['method' => __('orders.payment_methods.' . $method)]))
                                ->color('gray');
                        }

                        if ($record->channel?->name) {
                            $badges[] = Badge::make('channel')
                                ->label(__('orders.badges.channel', ['channel' => $record->channel->name]))
                                ->color('info');
                        }

                        return collect($badges)->filter()->values()->all();
                    })
                    ->suffixBadges(function (Order $record): array {
                        // Surface fulfillment progress, totals, and line counts adjacent to the status for at-a-glance triage.
                        $shippingState = match (true) {
                            filled($record->delivered_at) => 'delivered',
                            filled($record->shipped_at)   => 'shipped',
                            default                       => 'pending',
                        };

                        $shippingColor = match ($shippingState) {
                            'delivered' => 'success',
                            'shipped'   => 'info',
                            default     => 'warning',
                        };

                        $shippingDate = $shippingState === 'delivered'
                            ? optional($record->delivered_at)?->format('Y-m-d')
                            : ($shippingState === 'shipped' ? optional($record->shipped_at)?->format('Y-m-d') : null);

                        $itemsCount = (int) ($record->items_count ?? 0);

                        return collect([
                            Badge::make('shipping')
                                ->label(__('orders.badges.shipping.' . $shippingState, ['date' => $shippingDate]))
                                ->color($shippingColor),
                            Badge::make('total')
                                ->label(__('orders.badges.total', ['total' => Number::currency((float) $record->total, 'EUR', app()->getLocale())]))
                                ->color('gray'),
                            Badge::make('items')
                                ->label(trans_choice('orders.badges.items', $itemsCount, ['count' => $itemsCount]))
                                ->color($itemsCount > 0 ? 'primary' : 'gray'),
                        ])->filter()->values()->all();
                    })
                    ->tooltip(fn (Order $record): string => __('orders.badges.status_tooltip', ['number' => $record->number])),
                TextColumn::make('created_at')
                    ->label(__('orders.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('orders.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(function (): array {
                        // Bind the filter options to the enum-driven lifecycle so saved partner filters
                        // stay valid whenever additional statuses (e.g. returned) are introduced.
                        return OrderStatus::options();
                    })
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->options(function (): array {
                        // Keep the payment status filter aligned with the enum to expose authorized,
                        // captured, settled, and partially refunded states to partner operators.
                        return self::paymentStatusOptions();
                    })
                    ->multiple(),
                SelectFilter::make('payment_method')
                    ->options([
                        'credit_card'      => __('orders.payment_methods.credit_card'),
                        'bank_transfer'    => __('orders.payment_methods.bank_transfer'),
                        'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                        'paypal'           => __('orders.payment_methods.paypal'),
                        'stripe'           => __('orders.payment_methods.stripe'),
                        'apple_pay'        => __('orders.payment_methods.apple_pay'),
                        'google_pay'       => __('orders.payment_methods.google_pay'),
                    ])
                    ->multiple(),
                SelectFilter::make('channel')
                    ->relationship('channel', 'name')
                    ->preload(),
                TernaryFilter::make('is_paid')
                    ->label(__('orders.is_paid'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereIn('payment_status', ['paid', 'captured', 'settled', 'authorized']),
                        false: fn (Builder $query) => $query->whereNotIn('payment_status', ['paid', 'captured', 'settled', 'authorized']),
                    ),
                ValueRangeFilter::make('subtotal')
                    ->label(__('orders.fields.subtotal'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('discount_amount')
                    ->label(__('orders.fields.discount_amount'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('shipping_amount')
                    ->label(__('orders.fields.shipping_amount'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('total')
                    ->label(__('orders.fields.total'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('items_count')
                    ->label(__('orders.fields.items_count')),
                Filter::make('created_at')
                    ->label(__('orders.created_at'))
                    ->form([
                        SupportFlatpickr::makeRange('range', displayFormat: 'Y-m-d', format: 'Y-m-d'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
                TrashedFilter::make(),
            ])
            ->filtersFormWidth(Width::Large)
            ->headerActions([
                ExportAction::make('export')
                    ->label(__('Export'))
                    ->exports(self::getExportPresets()),
            ])
            ->actions([
                ViewAction::make()
                    ->color('info')
                    ->visible(fn (): bool => AuthorizationMatrix::check('orders', 'view')),
                EditAction::make()
                    ->color('warning')
                    ->visible(fn () => AuthorizationMatrix::check('orders', 'update')),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('orders', 'delete')),
                Action::make('mark_processing')
                    ->label(__('orders.mark_processing'))
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->visible(function (Order $record): bool {
                        $status = $record->status instanceof BackedEnum ? $record->status->value : (string) $record->status;

                        return AuthorizationMatrix::check('orders', 'update') && $status === 'pending';
                    }) // Keep the action hidden unless the operator can update and the order is pending.
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'processing']);
                        Notification::make()
                            ->title(__('orders.processing_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('mark_shipped')
                    ->label(__('orders.mark_shipped'))
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(function (Order $record): bool {
                        $status = $record->status instanceof BackedEnum ? $record->status->value : (string) $record->status;

                        return AuthorizationMatrix::check('orders', 'update') && $status === 'processing';
                    }) // Ensure only authorized staff can advance processing orders.
                    ->action(function (Order $record): void {
                        $record->update([
                            'status'     => 'shipped',
                            'shipped_at' => now(),
                        ]);
                        Notification::make()
                            ->title(__('orders.shipped_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('mark_delivered')
                    ->label(__('orders.mark_delivered'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function (Order $record): bool {
                        $status = $record->status instanceof BackedEnum ? $record->status->value : (string) $record->status;

                        return AuthorizationMatrix::check('orders', 'update') && $status === 'shipped';
                    }) // Restrict delivery confirmation to authorized operators.
                    ->action(function (Order $record): void {
                        $record->update([
                            'status'       => 'delivered',
                            'delivered_at' => now(),
                        ]);
                        Notification::make()
                            ->title(__('orders.delivered_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('cancel_order')
                    ->label(__('orders.cancel_order'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(function (Order $record): bool {
                        $status = $record->status instanceof BackedEnum ? $record->status->value : (string) $record->status;

                        return AuthorizationMatrix::check('orders', 'update') && in_array($status, ['pending', 'processing'], true);
                    }) // Combine status and permission gating for cancellation.
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()
                            ->title(__('orders.cancelled_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('refund_order')
                    ->label(__('orders.refund_order'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('secondary')
                    ->visible(function (Order $record): bool {
                        $status = $record->status instanceof BackedEnum ? $record->status->value : (string) $record->status;

                        return AuthorizationMatrix::check('orders', 'update') && in_array($status, ['delivered', 'completed'], true);
                    }) // Only show refunds for authorized users when fulfillment is complete.
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'refunded']);
                        Notification::make()
                            ->title(__('orders.refunded_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make('export_selected')
                        ->label(__('Export selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->exports(self::getExportPresets())
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn () => AuthorizationMatrix::check('orders', 'viewAny')),
                    DeleteBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('orders', 'delete')),
                    BulkAction::make('mark_processing')
                        ->label(__('orders.bulk_mark_processing'))
                        ->icon('heroicon-o-cog')
                        ->color('primary')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'processing']);
                            Notification::make()
                                ->title(__('orders.bulk_processing_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('orders', 'update')),
                    BulkAction::make('mark_shipped')
                        ->label(__('orders.bulk_mark_shipped'))
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status'     => 'shipped',
                                'shipped_at' => now(),
                            ]);
                            Notification::make()
                                ->title(__('orders.bulk_shipped_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('orders', 'update')),
                    BulkAction::make('mark_delivered')
                        ->label(__('orders.bulk_mark_delivered'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status'       => 'delivered',
                                'delivered_at' => now(),
                            ]);
                            Notification::make()
                                ->title(__('orders.bulk_delivered_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('orders', 'update')),
                    BulkAction::make('cancel_orders')
                        ->label(__('orders.bulk_cancel'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'cancelled']);
                            Notification::make()
                                ->title(__('orders.bulk_cancelled_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('orders', 'update')),
                    BulkAction::make('export_orders')
                        ->label(__('exports.actions.export_orders'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->form([
                            Select::make('format')
                                ->label(__('exports.form.format'))
                                ->options(collect(ExportFormat::cases())->mapWithKeys(fn (ExportFormat $format) => [$format->value => $format->label()])->all())
                                ->default(ExportFormat::Csv->value)
                                ->required(),
                            CheckboxList::make('columns')
                                ->label(__('exports.form.columns'))
                                ->options(self::exportColumnOptions())
                                ->default(array_keys(self::exportColumnOptions()))
                                ->columns(2)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            /** @var ExportService $exportService */
                            $exportService = app(ExportService::class);

                            $exportService->queueResourceExport(
                                resourceClass: self::class,
                                records: $records,
                                columnKeys: $data['columns'],
                                format: ExportFormat::from($data['format']),
                                requestedBy: auth()->user(),
                            );

                            Notification::make()
                                ->title(__('exports.notifications.queued'))
                                ->body(__('exports.notifications.queued_body'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('orders', 'viewAny')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * @return array<string, ExportColumn>
     */
    public static function availableExportColumns(): array
    {
        return [
            'number'         => new ExportColumn('number', self::translate('orders.number', 'Number'), resolver: fn (Order $order): string => (string) $order->number),
            'status'         => new ExportColumn('status', self::translate('orders.status', 'Status'), resolver: fn (Order $order): string => $order->status instanceof BackedEnum ? $order->status->value : (string) $order->status),
            'payment_status' => new ExportColumn('payment_status', self::translate('orders.payment_status', 'Payment Status'), resolver: fn (Order $order): string => $order->payment_status instanceof BackedEnum ? $order->payment_status->value : (string) $order->payment_status),
            'total'          => new ExportColumn('total', self::translate('orders.total', 'Total'), resolver: fn (Order $order): string => (string) $order->total),
            'customer'       => new ExportColumn('customer', self::translate('orders.customer', 'Customer'), resolver: fn (Order $order): string => (string) ($order->user?->name ?? '')),
            'created_at'     => new ExportColumn('created_at', self::translate('orders.created_at', 'Created At'), resolver: fn (Order $order): string => optional($order->created_at)->toDateTimeString() ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function exportColumnOptions(): array
    {
        return array_map(static fn (ExportColumn $column): string => $column->label, self::availableExportColumns());
    }

    private static function translate(string $key, string $fallback): string
    {
        $value = __($key);

        return is_string($value) && $value !== $key ? $value : $fallback;
    }

    /**
     * @return Builder<Order>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user:id,name,email',
                'channel:id,name',
            ])
            ->withCount(['items']);
    }

    /**
     * @return array<int, ExcelExport>
     */
    protected static function getExportPresets(): array
    {
        return [
            ExcelExport::make('visible_orders')
                ->label(__('orders.exports.visible_orders'))
                ->fromTable()
                ->queue()
                ->withChunkSize(500),
            ExcelExport::make('exportable_orders')
                ->label(__('orders.exports.exportable_orders'))
                ->fromTable()
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('exportable', true))
                ->queue()
                ->withColumns(self::getExportableOrderColumns()),
        ];
    }

    /**
     * Columns used for export presets.
     *
     * @return array<int, Column>
     */
    protected static function getExportableOrderColumns(): array
    {
        return [
            Column::make('number')
                ->heading(__('orders.fields.order_number')),
            Column::make('customer_email')
                ->heading(__('orders.fields.customer_email'))
                ->getStateUsing(fn (Order $record): ?string => $record->user?->email),
            Column::make('subtotal')
                ->heading(__('orders.fields.subtotal'))
                ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
                ->getStateUsing(fn (Order $record): float => (float) $record->subtotal),
            Column::make('tax_amount')
                ->heading(__('orders.fields.tax_amount'))
                ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
                ->getStateUsing(fn (Order $record): float => (float) $record->tax_amount),
            Column::make('shipping_amount')
                ->heading(__('orders.fields.shipping_amount'))
                ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
                ->getStateUsing(fn (Order $record): float => (float) $record->shipping_amount),
            Column::make('discount_amount')
                ->heading(__('orders.fields.discount_amount'))
                ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
                ->getStateUsing(fn (Order $record): float => (float) $record->discount_amount),
            Column::make('total')
                ->heading(__('orders.fields.total'))
                ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
                ->getStateUsing(fn (Order $record): float => (float) $record->total),
            Column::make('status')
                ->heading(__('orders.fields.status'))
                ->getStateUsing(function (Order $record): ?string {
                    $state = $record->status;

                    return $state instanceof BackedEnum ? $state->value : (is_string($state) ? $state : null);
                })
                ->formatStateUsing(fn (?string $state): string => $state ? __("orders.status.{$state}") : ''),
            Column::make('payment_status')
                ->heading(__('orders.fields.payment_status'))
                ->getStateUsing(function (Order $record): ?string {
                    $state = $record->payment_status;

                    return $state instanceof BackedEnum ? $state->value : (is_string($state) ? $state : null);
                })
                ->formatStateUsing(fn (?string $state): string => $state ? __("orders.payment_status.{$state}") : ''),
        ];
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function searchChannels(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Channel> $channels */
        $channels = Channel::query()
            ->select(['id', 'name', 'code'])
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $channels
            ->map(static function (Channel $channel): SearchResult {
                $label = self::formatChannelLabel($channel);

                return SearchResult::make((string) $channel->getKey(), $label);
            })
            ->all();
    }

    /**
     * Resolve the translated order status metadata from the enum so column badges and filters stay in sync.
     *
     * @return array{value:string,label:string,color:string,icon:string}
     */
    private static function resolveOrderStatusMeta(mixed $state): array
    {
        $enum = $state instanceof OrderStatus
            ? $state
            : (is_string($state) && $state !== '' ? OrderStatus::tryFrom($state) : null);

        if ($enum instanceof OrderStatus) {
            return [
                'value' => $enum->value,
                'label' => $enum->label(),
                'color' => $enum->color(),
                'icon'  => $enum->icon(),
            ];
        }

        $value = is_string($state) && $state !== '' ? $state : OrderStatus::PENDING->value;
        $label = __('orders.statuses.' . $value);

        if (! is_string($label) || $label === 'orders.statuses.' . $value) {
            // Fall back to a sensible humanised label when the translation catalogue lacks the entry.
            $label = Str::headline(str_replace('_', ' ', $value));
        }

        return [
            'value' => $value,
            'label' => $label,
            'color' => 'gray',
            'icon'  => 'heroicon-o-question-mark-circle',
        ];
    }

    /**
     * Resolve payment status metadata so badge colors and labels leverage the enriched enum.
     *
     * @return array{value:string,label:string,color:string}
     */
    private static function resolvePaymentStatusMeta(mixed $status): array
    {
        $enum = $status instanceof PaymentStatus
            ? $status
            : (is_string($status) && $status !== '' ? PaymentStatus::tryFrom($status) : null);

        $value = $enum?->value ?? (is_string($status) && $status !== '' ? $status : PaymentStatus::PENDING->value);
        $label = __('orders.payment_statuses.' . $value);

        if (! is_string($label) || $label === 'orders.payment_statuses.' . $value) {
            // Ensure every enum case still renders a pleasant label even when localisation is pending.
            $label = Str::headline(str_replace('_', ' ', $value));
        }

        $color = match ($value) {
            PaymentStatus::PAID->value,
            PaymentStatus::AUTHORIZED->value,
            PaymentStatus::CAPTURED->value,
            PaymentStatus::SETTLED->value => 'success',
            PaymentStatus::FAILED->value => 'danger',
            PaymentStatus::REFUNDED->value,
            PaymentStatus::PARTIALLY_REFUNDED->value => 'secondary',
            default => 'warning',
        };

        return [
            'value' => $value,
            'label' => $label,
            'color' => $color,
        ];
    }

    /**
     * Provide translated payment status options derived from the enum for filters and forms.
     */
    private static function paymentStatusOptions(): array
    {
        return collect(PaymentStatus::cases())
            ->mapWithKeys(function (PaymentStatus $status): array {
                $label = __('orders.payment_statuses.' . $status->value);

                if (! is_string($label) || $label === 'orders.payment_statuses.' . $status->value) {
                    $label = Str::headline(str_replace('_', ' ', $status->value));
                }

                return [$status->value => $label];
            })
            ->toArray();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function searchPartners(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Partner> $partners */
        $partners = Partner::query()
            ->select(['id', 'name', 'code'])
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $partners
            ->map(static function (Partner $partner): SearchResult {
                $label = self::formatPartnerLabel($partner);

                return SearchResult::make((string) $partner->getKey(), $label);
            })
            ->all();
    }

    private static function formatChannelLabel(?Channel $channel): string
    {
        if (! $channel instanceof Channel) {
            return '';
        }

        $code = $channel->getAttribute('code');
        $name = $channel->getAttribute('name');

        return trim(sprintf('[%s] %s', $code !== null && $code !== '' ? $code : '—', (string) ($name ?? '')));
    }

    private static function formatPartnerLabel(?Partner $partner): string
    {
        if (! $partner instanceof Partner) {
            return '';
        }

        $code = $partner->getAttribute('code');
        $name = $partner->getAttribute('name');

        return trim(sprintf('[%s] %s', $code !== null && $code !== '' ? $code : '—', (string) ($name ?? '')));
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
            RelationManagers\OrderShippingRelationManager::class,
            RelationManagers\OrderDocumentsRelationManager::class,
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Get the global search result details.
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Customer' => $record->user->name ?? 'N/A',
            'Total'    => '€' . number_format((float) $record->total, 2),
            'Status'   => __("orders.statuses.{$record->status}"),
        ];
    }

    /**
     * Get the global search result actions.
     */
    public static function getGlobalSearchResultActions($record): array
    {
        $actions = [];

        try {
            if ($record instanceof Order && self::canView($record)) {
                $actions[] = PageAction::make('view')
                    ->label(__('orders.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(self::getUrl('view', ['record' => $record]));
            }
        } catch (Exception $e) {
            // Route might not exist, skip this action
        }

        try {
            if ($record instanceof Order && self::canEdit($record)) {
                $actions[] = PageAction::make('edit')
                    ->label(__('orders.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(self::getUrl('edit', ['record' => $record]));
            }
        } catch (Exception $e) {
            // Route might not exist, skip this action
        }

        return $actions;
    }
}
