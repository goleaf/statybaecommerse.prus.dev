<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.checkout_order_information'))
                    ->schema([
                        TextInput::make('number')
                            ->label(__('messages.order_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('status')
                            ->label(__('messages.status'))
                            ->options(OrderStatus::options())
                            ->getOptionLabelUsing(static fn ($value): ?string => OrderStatus::tryFrom(self::normalizeEnumValue($value))?->label() ?? Str::headline(self::normalizeEnumValue($value)))
                            ->required()
                            ->default(OrderStatus::PENDING),
                        Hidden::make('currency')
                            ->default('EUR'),
                    ])->columns(3)
                    ->columnSpanFull(),

                Section::make(__('messages.customer'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('messages.customer'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->default(static fn (): ?int => request()->integer('user_id') ?: null)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $user = User::find($state);
                                if (! $user) {
                                    return;
                                }

                                $address = Address::where('user_id', $state)->where('is_default', true)->first()
                                    ?? Address::where('user_id', $state)->first();

                                if ($address) {
                                    $set('address_selector', $address->id);
                                    $set('billing_address_selector', $address->id);

                                    $set('shipping_address.first_name', $address->first_name);
                                    $set('shipping_address.last_name', $address->last_name);
                                    $set('shipping_address.email', $address->email);
                                    $set('shipping_address.phone', $address->phone);
                                    $set('shipping_address.street', trim($address->address_line_1 . ($address->address_line_2 ? ', ' . $address->address_line_2 : '')));
                                    $set('shipping_address.city', $address->city);
                                    $set('shipping_address.zip', $address->postal_code);
                                    $set('shipping_address.country', $address->country?->name ?? $address->country_code);

                                    $set('billing_address.first_name', $address->first_name);
                                    $set('billing_address.last_name', $address->last_name);
                                    $set('billing_address.email', $address->email);
                                    $set('billing_address.phone', $address->phone);
                                    $set('billing_address.street', trim($address->address_line_1 . ($address->address_line_2 ? ', ' . $address->address_line_2 : '')));
                                    $set('billing_address.city', $address->city);
                                    $set('billing_address.zip', $address->postal_code);
                                    $set('billing_address.country', $address->country?->name ?? $address->country_code);
                                } else {
                                    $set('address_selector', null);
                                    $set('billing_address_selector', null);

                                    $set('shipping_address.first_name', $user->first_name);
                                    $set('shipping_address.last_name', $user->last_name);
                                    $set('shipping_address.email', $user->email);
                                    $set('shipping_address.phone', $user->phone ?? $user->phone_number);

                                    $set('billing_address.first_name', $user->first_name);
                                    $set('billing_address.last_name', $user->last_name);
                                    $set('billing_address.email', $user->email);
                                    $set('billing_address.phone', $user->phone ?? $user->phone_number);
                                }
                            })
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required(),
                                TextInput::make('email')
                                    ->label(__('messages.email'))
                                    ->required()->email(),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make(__('messages.items'))
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('messages.product'))
                                    ->relationship('product', 'name')
                                    ->searchable(['name', 'sku'])
                                    ->getOptionLabelFromRecordUsing(fn (Product $record) => view('filament.forms.components.product-select-option', ['product' => $record])->render())
                                    ->getSearchResultsUsing(function (Select $component, string $search): array {
                                        return Product::query()
                                            ->where('name', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn (Product $record) => [$record->getKey() => $component->getOptionLabelFromRecord($record)])
                                            ->toArray();
                                    })
                                    ->allowHtml()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set): void {
                                        $set('unit_price', Product::find($state)?->price ?? 0);
                                    })
                                    ->required()
                                    ->columnSpan(3),
                                TextInput::make('quantity')
                                    ->label(__('messages.quantity'))
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('unit_price')
                                    ->label(__('messages.unit_price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(5)
                            ->defaultItems(0),
                    ])
                    ->columnSpanFull(),
                Section::make(__('translations.services'))
                    ->schema([
                        Repeater::make('services')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        Select::make('service_id')
                                            ->label(__('translations.service'))
                                            ->options(static fn (): array => Service::query()
                                                ->where('is_active', true)
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(3),
                                        TextInput::make('quantity')
                                            ->label(__('messages.quantity'))
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->columnSpan(1),
                                        TextInput::make('price')
                                            ->label(__('messages.price'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->required()
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->defaultItems(0)
                            ->visible(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columnSpanFull(),

                Section::make(__('messages.checkout_shipping_address'))
                    ->schema([
                        Select::make('address_selector')
                            ->label(__('translations.customer_addresses'))
                            ->options(fn (Get $get): array => Address::where('user_id', $get('user_id'))
                                ->get()
                                ->mapWithKeys(fn (Address $address) => [
                                    $address->id => "
                                        <div class='flex flex-col'>
                                            <span class='font-bold'>{$address->full_name}</span>
                                            <span class='text-xs text-gray-500'>{$address->full_address}</span>
                                        </div>
                                    ",
                                ])
                                ->toArray())
                            ->allowHtml()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $address = Address::find($state);
                                if (! $address) {
                                    return;
                                }

                                $set('shipping_address.first_name', $address->first_name);
                                $set('shipping_address.last_name', $address->last_name);
                                $set('shipping_address.email', $address->email);
                                $set('shipping_address.phone', $address->phone);
                                $set('shipping_address.street', trim($address->address_line_1 . ($address->address_line_2 ? ', ' . $address->address_line_2 : '')));
                                $set('shipping_address.city', $address->city);
                                $set('shipping_address.zip', $address->postal_code);
                                $set('shipping_address.country', $address->country?->name ?? $address->country_code);
                                $set('shipping_address_is_default', $address->is_default);
                            })
                            ->visible(fn (Get $get): bool => filled($get('user_id')))
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('shipping_address.first_name')->label(__('messages.first_name')),
                        TextInput::make('shipping_address.last_name')->label(__('messages.last_name')),
                        TextInput::make('shipping_address.email')->email()->label(__('messages.email')),
                        TextInput::make('shipping_address.phone')->tel()->label(__('messages.phone')),
                        TextInput::make('shipping_address.street')->label(__('messages.street'))->columnSpanFull(),
                        TextInput::make('shipping_address.city')->label(__('messages.city')),
                        TextInput::make('shipping_address.zip')->label(__('messages.zip_code')),
                        TextInput::make('shipping_address.country')->label(__('messages.country')),
                        \Filament\Forms\Components\Checkbox::make('shipping_address_is_default')
                            ->label(__('messages.is_default'))
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get) {
                                if ($state && $get('address_selector')) {
                                    $address = Address::find($get('address_selector'));
                                    if ($address) {
                                        $address->setAsDefault();
                                        \Filament\Notifications\Notification::make()->title(__('messages.address_set_as_default'))->success()->send();
                                    }
                                }
                            })
                            ->columnSpanFull(),
                    ])->columns(4)
                    ->columnSpanFull(),

                Section::make(__('messages.checkout_billing_address'))
                    ->schema([
                        Select::make('billing_address_selector')
                            ->label(__('translations.customer_addresses'))
                            ->options(fn (Get $get): array => Address::where('user_id', $get('user_id'))
                                ->get()
                                ->mapWithKeys(fn (Address $address) => [
                                    $address->id => "
                                        <div class='flex flex-col'>
                                            <span class='font-bold'>{$address->full_name}</span>
                                            <span class='text-xs text-gray-500'>{$address->full_address}</span>
                                        </div>
                                    ",
                                ])
                                ->toArray())
                            ->allowHtml()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $address = Address::find($state);
                                if (! $address) {
                                    return;
                                }

                                $set('billing_address.first_name', $address->first_name);
                                $set('billing_address.last_name', $address->last_name);
                                $set('billing_address.email', $address->email);
                                $set('billing_address.phone', $address->phone);
                                $set('billing_address.street', trim($address->address_line_1 . ($address->address_line_2 ? ', ' . $address->address_line_2 : '')));
                                $set('billing_address.city', $address->city);
                                $set('billing_address.zip', $address->postal_code);
                                $set('billing_address.country', $address->country?->name ?? $address->country_code);
                                $set('billing_address_is_default', $address->is_default);
                            })
                            ->visible(fn (Get $get): bool => filled($get('user_id')))
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('billing_address.first_name')->label(__('messages.first_name')),
                        TextInput::make('billing_address.last_name')->label(__('messages.last_name')),
                        TextInput::make('billing_address.email')->email()->label(__('messages.email')),
                        TextInput::make('billing_address.phone')->tel()->label(__('messages.phone')),
                        TextInput::make('billing_address.street')->label(__('messages.street'))->columnSpanFull(),
                        TextInput::make('billing_address.city')->label(__('messages.city')),
                        TextInput::make('billing_address.zip')->label(__('messages.zip_code')),
                        TextInput::make('billing_address.country')->label(__('messages.country')),
                        \Filament\Forms\Components\Checkbox::make('billing_address_is_default')
                            ->label(__('messages.is_default'))
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get) {
                                if ($state && $get('billing_address_selector')) {
                                    $address = Address::find($get('billing_address_selector'));
                                    if ($address) {
                                        $address->setAsDefault();
                                        \Filament\Notifications\Notification::make()->title(__('messages.address_set_as_default'))->success()->send();
                                    }
                                }
                            })
                            ->columnSpanFull(),
                    ])->columns(4)
                    ->columnSpanFull(),

                Section::make(__('messages.financials'))
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(__('messages.subtotal'))
                            ->numeric()->prefix('€')
                            ->default(0),
                        TextInput::make('shipping_amount')
                            ->label(__('messages.shipping'))
                            ->numeric()->prefix('€')
                            ->default(0),
                        TextInput::make('tax_amount')
                            ->label(__('messages.tax_amount'))
                            ->numeric()->prefix('€')
                            ->default(0),
                        TextInput::make('discount_amount')
                            ->label(__('messages.discount_amount'))
                            ->numeric()->prefix('€')
                            ->default(0),
                        TextInput::make('total')
                            ->label(__('messages.total'))
                            ->numeric()->prefix('€')->required(),
                    ])->columns(5)
                    ->columnSpanFull(),

                Section::make(__('messages.checkout_payment'))
                    ->schema([
                        Select::make('payment_method')
                            ->label(__('messages.payment_method'))
                            ->options(PaymentMethod::options())
                            ->getOptionLabelUsing(static fn ($value): ?string => PaymentMethod::tryFrom(self::normalizeEnumValue($value))?->getLabel() ?? Str::headline(self::normalizeEnumValue($value))),
                        Select::make('payment_status')
                            ->label(__('messages.payment_status'))
                            ->options(PaymentStatus::options())
                            ->getOptionLabelUsing(static fn ($value): ?string => PaymentStatus::tryFrom(self::normalizeEnumValue($value))?->getLabel() ?? Str::headline(self::normalizeEnumValue($value)))
                            ->required(),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.dates'))
                    ->schema([
                        DateTimePicker::make('created_at')
                            ->label(__('messages.created_at'))
                            ->disabled(),
                        DateTimePicker::make('updated_at')
                            ->label(__('messages.updated_at'))
                            ->disabled(),
                        DateTimePicker::make('shipped_at')
                            ->label(__('messages.shipped_at')),
                        DateTimePicker::make('delivered_at')
                            ->label(__('messages.delivered_at')),
                    ])->columns(4)
                    ->columnSpanFull(),
            ]);
    }

    private static function normalizeEnumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if (is_scalar($value) || $value === null) {
            return (string) ($value ?? '');
        }

        return '';
    }
}
