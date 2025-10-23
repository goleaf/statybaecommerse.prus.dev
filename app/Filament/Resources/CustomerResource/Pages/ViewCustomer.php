<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Address;
use App\Models\Customer;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\ListGroup\Entries\ListItem;
use LaraZeus\ListGroup\Infolists\ListEntry;

final class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $customer = $this->record->loadMissing(['orders']);

        $locale = app()->getLocale();

        $resolveTranslation = static function (mixed $model, string $field) use ($locale): mixed {
            if (method_exists($model, 'getTranslation')) {
                $value = $model->getTranslation($field, $locale);
                if (filled($value)) {
                    return $value;
                }
            }

            if (method_exists($model, 'trans')) {
                $value = $model->trans($field, $locale);
                if (filled($value)) {
                    return $value;
                }
            }

            return $model->{$field} ?? null;
        };

        $customerName = $resolveTranslation($customer, 'name') ?? $customer->name;

        $quickLinks = array_values(array_filter([
            ListItem::make()
                ->id('customer-orders-'.$customer->getKey())
                ->label(__('View storefront account orders'))
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary')
                ->url(route('frontend.orders.index'))
                ->tooltip(__('Open the customer order history page.')),
            filled($customer->email)
                ? ListItem::make()
                    ->id('customer-email-'.$customer->getKey())
                    ->label(__('Email :name', ['name' => $customerName ?? $customer->email]))
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->url('mailto:'.$customer->email)
                    ->tooltip(__('Compose an email to :name', ['name' => $customerName ?? $customer->email]))
                : null,
            filled($customer->phone)
                ? ListItem::make()
                    ->id('customer-phone-'.$customer->getKey())
                    ->label(__('Call :name', ['name' => $customerName ?? $customer->phone]))
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->url('tel:'.$customer->phone)
                    ->tooltip(__('Dial :phone for :name', ['phone' => $customer->phone, 'name' => $customerName ?? $customer->phone]))
                : null,
        ]));

        $orderItems = $customer->orders
            ->sortByDesc('created_at')
            ->take(5)
            ->map(function ($order) use ($customerName) {
                return ListItem::make()
                    ->id('customer-order-'.$order->getKey())
                    ->label(__('Order #:number', ['number' => $order->number]))
                    ->icon('heroicon-o-receipt-percent')
                    ->color('warning')
                    ->url(route('frontend.orders.show', ['order' => $order->getKey()]))
                    ->badge(strtoupper((string) $order->status))
                    ->tooltip(__('View order #:number placed by :customer', ['number' => $order->number, 'customer' => $customerName ?? __('the customer')]));
            })
            ->values()
            ->all();

        return $infolist->schema([
            ListEntry::make('customer_quick_links')
                ->heading(__('Quick links'))
                ->state(fn () => $quickLinks),
            ListEntry::make('customer_orders')
                ->heading(__('Recent orders'))
                ->list()
                ->state(fn () => $orderItems),
        ]);
    }
}
