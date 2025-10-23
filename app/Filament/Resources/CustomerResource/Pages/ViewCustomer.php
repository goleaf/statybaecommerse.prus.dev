<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\ReviewResource;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Review;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Illuminate\Support\Str;
use LaraZeus\ListGroup\Entries\ListItem;
use LaraZeus\ListGroup\Infolists\ListEntry;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;

final class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        // Configure the Filament infolist schema using the v4 Schema API.
        return $schema->schema([
            ListEntry::make('customerQuickLinks')
                ->heading(__('Quick links'))
                ->list()
                ->state(function (Customer $record): array {
                    // Provide quick actions with consistent formatting for localized admins.
                    $items = [];

                    if (filled($record->email)) {
                        $items[] = ListItem::make()
                            ->id('customer-email-link')
                            ->label(__('Email :name', ['name' => $record->name]))
                            ->icon('heroicon-m-envelope')
                            ->color('primary')
                            ->url('mailto:' . $record->email)
                            ->tooltip(__('Compose an email to :email', ['email' => $record->email]))
                            ->toArray();
                    }

                    if (filled($record->phone)) {
                        $items[] = ListItem::make()
                            ->id('customer-phone-link')
                            ->label(__('Call :name', ['name' => $record->name]))
                            ->icon('heroicon-m-phone')
                            ->color('success')
                            ->url('tel:' . preg_replace('/[^\d+]/', '', (string) $record->phone))
                            ->tooltip(__('Dial :phone', ['phone' => $record->phone]))
                            ->toArray();
                    }

                    return $items;
                }),
            ListEntry::make('customerOrders')
                ->heading(__('customers.orders'))
                ->list()
                ->state(function (Customer $record): array {
                    // Pull the latest orders and render localized metadata for each list entry.
                    $record->loadMissing(['orders']);

                    return $record->orders
                        ->sortByDesc('created_at')
                        ->map(function (Order $order): array {
                            return ListItem::make()
                                ->id('customer-order-' . $order->getKey())
                                ->label(__('customers.order_number_label', ['number' => $order->number]))
                                ->icon('heroicon-m-receipt-percent')
                                ->color('info')
                                ->url(route('account.orders.detail', ['number' => $order->number]))
                                ->tooltip(__('customers.order_placed_on', [
                                    'date' => optional($order->created_at)->toFormattedDateString(),
                                ]))
                                ->toArray();
                        })
                        ->all();
                }),
            ListEntry::make('customerReviews')
                ->heading(__('customers.reviews'))
                ->list()
                ->state(function (Customer $record): array {
                    // Load related products and translate review metadata per locale.
                    $locale = app()->getLocale();
                    $record->loadMissing(['reviews.product']);

                    return $record->reviews
                        ->sortByDesc('created_at')
                        ->map(function (Review $review) use ($locale): array {
                            $productName = $review->product?->getTranslation('name', $locale) ?? $review->product?->name ?? __('products.title');
                            $reviewTitle = $review->getTranslation('title', $locale) ?? Str::limit($review->getTranslation('content', $locale) ?? '', 40);

                            return ListItem::make()
                                ->id('customer-review-' . $review->getKey())
                                ->label(__('customers.review_for_product', ['product' => $productName]))
                                ->icon('heroicon-m-star')
                                ->color('warning')
                                ->url(ReviewResource::getUrl('view', ['record' => $review]))
                                ->tooltip(__('customers.review_rating_tooltip', [
                                    'rating' => $review->rating,
                                    'title'  => $reviewTitle,
                                ]))
                                ->toArray();
                        })
                        ->all();
                }),
            Section::make(__('customers.address_information'))
                ->schema([
                    TableRepeatableEntry::make('addresses')
                        ->label(__('customers.address_information'))
                        ->translateLabel()
                        ->state(function (Customer $record): array {
                            $record->loadMissing(['addresses.country', 'addresses.cityById']);

                            return $record->addresses
                                ->map(fn (Address $address): array => [
                                    'name'        => $address->full_name,
                                    'address'     => $address->formatted_address ?? $address->address_line_1,
                                    'city'        => $address->city ?: $address->cityById?->name,
                                    'postal_code' => $address->postal_code,
                                    'country'     => $address->country?->name ?? $address->country_code,
                                    'phone'       => $address->phone,
                                ])
                                ->values()
                                ->all();
                        })
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('customers.name'))
                                ->translateLabel(),
                            TextEntry::make('address')
                                ->label(__('customers.address'))
                                ->translateLabel(),
                            TextEntry::make('city')
                                ->label(__('customers.city'))
                                ->translateLabel(),
                            TextEntry::make('postal_code')
                                ->label(__('customers.postal_code'))
                                ->translateLabel(),
                            TextEntry::make('country')
                                ->label(__('customers.country'))
                                ->translateLabel(),
                            TextEntry::make('phone')
                                ->label(__('customers.phone'))
                                ->translateLabel(),
                        ])
                        ->striped()
                        ->showIndex(),
                ])
                ->columns(1),
        ]);
    }
}