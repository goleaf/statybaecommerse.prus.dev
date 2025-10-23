<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Data\ExportRequestData;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Address;
use App\Models\Channel;
use App\Models\Order;
use App\Services\Pricing\PriceCalculator;
use App\Services\Export\ExportColumn;
use App\Services\Export\Exporters\OrderExport;
use App\Services\Export\ExportService;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
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
use UnitEnum;

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
    use HasNav;

    protected static ?string $model = Order::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $navigationLabel = 'orders.navigation.orders';

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', Order::class);
    }

    public static function canView(Order $record): bool
    {
        return Gate::allows('view', $record);
    }

    public static function canCreate(): bool
    {
        return Gate::allows('create', Order::class);
    }

    public static function canEdit(Order $record): bool
    {
        return Gate::allows('update', $record);
    }

    public static function canDelete(Order $record): bool
    {
        return Gate::allows('delete', $record);
    }

    protected static ?string $modelLabel = 'orders.models.order';

    protected static ?string $pluralModelLabel = 'orders.models.orders';

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('orders', 'viewAny');
    }

    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check('orders', 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check('orders', 'view');
    }

    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check('orders', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check('orders', 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('orders', 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('orders', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('orders', 'update');
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
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
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('orders.sections.order_details'))
                ->description(__('orders.sections.customer_information'))
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Grid::make(3)
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
                                ->afterStateUpdated(function (?string $state, Set $set): void {
                                    if ($state === null || $state === '') {
                                        // Reset relation when lookup clears.
                                        SearchableInputHelper::clear($set, ['user_id' => null]);

                                        return;
                                    }

                                    $set('user_id', (int) $state);
                                }),
                            Select::make('status')
                                ->label(__('orders.fields.status'))
                                ->options([
                                    'pending'    => __('orders.status.pending'),
                                    'processing' => __('orders.status.processing'),
                                    'shipped'    => __('orders.status.shipped'),
                                    'delivered'  => __('orders.status.delivered'),
                                    'cancelled'  => __('orders.status.cancelled'),
                                    'refunded'   => __('orders.status.refunded'),
                                ])
                                ->default('pending'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('payment_status')
                                ->label(__('orders.fields.payment_status'))
                                ->options([
                                    'pending'  => __('orders.payment_status.pending'),
                                    'paid'     => __('orders.payment_status.paid'),
                                    'failed'   => __('orders.payment_status.failed'),
                                    'refunded' => __('orders.payment_status.refunded'),
                                ]),
                            Select::make('payment_method')
                                ->label(__('orders.fields.payment_method'))
                                ->options([
                                    'credit_card'      => __('orders.payment_methods.credit_card'),
                                    'bank_transfer'    => __('orders.payment_methods.bank_transfer'),
                                    'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                                    'paypal' => __('orders.payment_methods.paypal'),
                                    'stripe' => __('orders.payment_methods.stripe'),
                                    'apple_pay' => __('orders.payment_methods.apple_pay'),
                                    'google_pay' => __('orders.payment_methods.google_pay'),
                                ]),
                            TextInput::make('payment_reference')
                                ->label(__('orders.fields.payment_reference')),
                        ]),
                ])
                ->collapsible(),
            Section::make(__('orders.sections.order_details'))
                ->description(__('orders.fields.total'))
                ->icon('heroicon-o-currency-euro')
                ->schema([
                    Grid::make(4)
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

                            return $breakdown->total;
                        }),
                ])
                ->collapsible(),
            Section::make(__('orders.sections.billing_information'))
                ->description(__('orders.sections.shipping_information'))
                ->icon('heroicon-o-map-pin')
                ->schema([
                    Grid::make(2)
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
                                        static fn (int $value): ?array => ['value' => $value, 'label' => (string) $value],
                                    );
                                })
                                // See docs/forms/SEARCHABLE_INPUT_METADATA.md for SearchResult metadata conventions.
                                ->afterStateUpdated(function (?int $state, Set $set): void {
                                    if ($state === null) {
                                        // Reset the cached billing payload when cleared.
                                        SearchableInputHelper::clear($set, ['billing_address' => []]);

                                        return;
                                    }

                                    $address = Address::query()
                                        ->select(['id', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code'])
                                        ->find($state);

                                    if (! $address instanceof Address) {
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
                                        static fn (int $value): ?array => ['value' => $value, 'label' => (string) $value],
                                    );
                                })
                                // See docs/forms/SEARCHABLE_INPUT_METADATA.md for SearchResult metadata conventions.
                                ->afterStateUpdated(function (?int $state, Set $set): void {
                                    if ($state === null) {
                                        SearchableInputHelper::clear($set, ['shipping_address' => []]);

                                        return;
                                    }

                                    $address = Address::query()
                                        ->select(['id', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code'])
                                        ->find($state);

                                    if (! $address instanceof Address) {
                                        return;
                                    }

                                    $set('shipping_address', AddressSearch::payload($address));
                                })
                                ->dehydrated(false),
                        ]),
                    Grid::make(2)
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
            Section::make(__('orders.sections.order_shipping'))
                ->description(__('orders.sections.shipping_information'))
                ->icon('heroicon-o-truck')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Flatpickr::makeDateTime('shipped_at')
                                ->label(__('orders.fields.shipped_at')),
                            Flatpickr::makeDateTime('delivered_at')
                                ->label(__('orders.fields.delivered_at')),
                        ]),
                    TextInput::make('tracking_number')
                        ->label(__('orders.fields.tracking_number'))
                        ->maxLength(255),
                ])
                ->collapsible(),
            Section::make(__('orders.sections.order_details'))
                ->description(__('orders.fields.notes'))
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('notes')
                        ->label(__('orders.fields.notes'))
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText(__('orders.fields.internal_notes')),
                    Grid::make(3)
                        ->schema([
                            Select::make('channel_id')
                                ->label(__('orders.fields.channel'))
                                ->relationship('channel', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('partner_id')
                                ->label(__('orders.fields.partner'))
                                ->relationship('partner', 'name')
                                ->searchable()
                                ->preload(),
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
        $formats = config('export.formats', []);

        if ($formats === []) {
            $formats = ['csv' => \App\Services\Export\Writers\CsvExportWriter::class];
        }

        $formatOptions = collect(array_keys($formats))
            ->mapWithKeys(fn (string $format): array => [$format => strtoupper($format)])
            ->all();

        $defaultFormat = array_key_first($formats) ?? 'csv';

        return $table
            ->columns([
                TextColumn::make('number')
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
                BadgeColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->colors([
                        'warning'   => 'pending',
                        'primary'   => 'processing',
                        'info'      => 'shipped',
                        'success'   => 'delivered',
                        'danger'    => 'cancelled',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("orders.status.{$state}"))
                    ->sortable(),
                BadgeColumn::make('payment_status')
                    ->label(__('orders.fields.payment_status'))
                    ->colors([
                        'warning'   => 'pending',
                        'success'   => 'paid',
                        'danger'    => 'failed',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("orders.payment_status.{$state}"))
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('orders.fields.total'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('orders.fields.items_count'))
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label(__('orders.fields.payment_method'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __("orders.payment_methods.{$state}") : '-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('channel.name')
                    ->label(__('orders.fields.channel'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
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
                    ->options([
                        'pending'    => __('orders.statuses.pending'),
                        'processing' => __('orders.statuses.processing'),
                        'shipped'    => __('orders.statuses.shipped'),
                        'delivered'  => __('orders.statuses.delivered'),
                        'cancelled'  => __('orders.statuses.cancelled'),
                        'refunded'   => __('orders.statuses.refunded'),
                    ])
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->options([
                        'pending'  => __('orders.payment_statuses.pending'),
                        'paid'     => __('orders.payment_statuses.paid'),
                        'failed'   => __('orders.payment_statuses.failed'),
                        'refunded' => __('orders.payment_statuses.refunded'),
                    ])
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
                    ->form([
                        Flatpickr::makeRange('range')
                            ->label(__('orders.created_at'))

                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('total_range')
                    ->form([
                        TextInput::make('total_from')
                            ->label(__('orders.total_from'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('total_until')
                            ->label(__('orders.total_until'))
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_from'],
                                fn (Builder $query, $amount): Builder => $query->where('total', '>=', $amount),
                            )
                            ->when(
                                $data['total_until'],
                                fn (Builder $query, $amount): Builder => $query->where('total', '<=', $amount),
                            );
                    }),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()
                    ->color('info')
                    ->visible(fn () => AuthorizationMatrix::check('orders', 'view')),
                EditAction::make()
                    ->color('warning')
                    ->visible(fn () => AuthorizationMatrix::check('orders', 'update')),
                \Filament\Tables\Actions\DeleteAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('orders', 'delete')),
                Action::make('mark_processing')
                    ->label(__('orders.mark_processing'))
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->visible(fn (Order $record): bool => AuthorizationMatrix::check('orders', 'update') && $record->status === 'pending')
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
                    ->visible(fn (Order $record): bool => AuthorizationMatrix::check('orders', 'update') && $record->status === 'processing')
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
                    ->visible(fn (Order $record): bool => AuthorizationMatrix::check('orders', 'update') && $record->status === 'shipped')
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
                    ->visible(fn (Order $record): bool => AuthorizationMatrix::check('orders', 'update') && in_array($record->status, ['pending', 'processing']))
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
                    ->visible(fn (Order $record): bool => AuthorizationMatrix::check('orders', 'update') && in_array($record->status, ['delivered', 'completed']))
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
                    BulkAction::make('export_selected')
                        ->label(__('exports.filament.bulk_action.label'))
                        ->modalHeading(__('exports.filament.bulk_action.modal_heading', ['label' => self::getPluralModelLabel()]))
                        ->modalDescription(__('exports.filament.bulk_action.modal_description'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->form([
                            Select::make('format')
                                ->label(__('exports.filament.bulk_action.format_label'))
                                ->options($formatOptions)
                                ->default($defaultFormat)
                                ->required(),
                            CheckboxList::make('columns')
                                ->label(__('exports.filament.bulk_action.columns_label'))
                                ->options(fn () => collect(app(OrderExport::class)->columns())->mapWithKeys(fn (ExportColumn $column) => [$column->key => $column->label])->all())
                                ->default(fn () => app(OrderExport::class)->defaultColumns())
                                ->columns(2)
                                ->helperText(__('exports.filament.bulk_action.columns_help'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            /** @var ExportService $service */
                            $service = app(ExportService::class);
                            $columns = $data['columns'] ?? app(OrderExport::class)->defaultColumns();
                            $request = new ExportRequestData(
                                name: __('Orders Export'),
                                exportable: OrderExport::class,
                                format: $data['format'],
                                columns: $columns,
                                recordIds: $records->pluck('id')->all(),
                                userId: auth()->id(),
                            );

                            $service->queue($request);

                            Notification::make()
                                ->title(__('exports.filament.bulk_action.success'))
                                ->body(__('exports.filament.bulk_action.success_body'))
                                ->success()
                                ->send();
                        })
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

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::authorizeOrder(null, 'viewAny');
    }

    public static function canCreate(): bool
    {
        return static::authorizeOrder(null, 'create');
    }

    public static function canView(Order $record): bool
    {
        return static::authorizeOrder($record, 'view');
    }

    public static function canEdit(Order $record): bool
    {
        return static::authorizeOrder($record, 'update');
    }

    public static function canDelete(Order $record): bool
    {
        return static::authorizeOrder($record, 'delete');
    }

    public static function canRestore(Order $record): bool
    {
        return static::authorizeOrder($record, 'restore');
    }

    /**
     * @return array<string, ExportColumn>
     */
    public static function availableExportColumns(): array
    {
        return [
            'number' => new ExportColumn('number', __('orders.number'), fn (Order $order): string => (string) $order->number),
            'status' => new ExportColumn('status', __('orders.status'), fn (Order $order): string => (string) $order->status),
            'payment_status' => new ExportColumn('payment_status', __('orders.payment_status'), fn (Order $order): string => (string) $order->payment_status),
            'total' => new ExportColumn('total', __('orders.total'), fn (Order $order): string => (string) $order->total),
            'customer' => new ExportColumn('customer', __('orders.customer'), fn (Order $order): string => (string) ($order->user?->name ?? '')),
            'created_at' => new ExportColumn('created_at', __('orders.created_at'), fn (Order $order): string => optional($order->created_at)->toDateTimeString() ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function exportColumnOptions(): array
    {
        return array_map(static fn (ExportColumn $column): string => $column->label, self::availableExportColumns());
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
            if ($record instanceof Order && static::canView($record)) {
                $actions[] = PageAction::make('view')
                    ->label(__('orders.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(self::getUrl('view', ['record' => $record]));
            }
        } catch (\Exception $e) {
            // Route might not exist, skip this action
        }

        try {
            if ($record instanceof Order && static::canEdit($record)) {
                $actions[] = PageAction::make('edit')
                    ->label(__('orders.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(self::getUrl('edit', ['record' => $record]));
            }
        } catch (\Exception $e) {
            // Route might not exist, skip this action
        }

        return $actions;
    }

    private static function authorizeOrder(?Order $order, string $ability): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $order instanceof Order
            ? $user->can($ability, $order)
            : $user->can($ability, Order::class);
    }
}
