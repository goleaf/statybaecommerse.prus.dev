<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\OrderPlaced;
use App\Models\Country;
use App\Models\Order;
use App\Services\Cart\CartLifecycleService;
use App\Services\Pricing\PriceCalculator;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Number;
use Throwable;

/**
 * CreateOrder
 *
 * Action class for CreateOrder single-purpose operations with validation, error handling, and result reporting.
 */
final readonly class CreateOrder
{
    public function handle(): Order
    {
        /** @var array<string, mixed>|null $checkout */
        $checkout = session()->get('checkout');
        if (! is_array($checkout)) {
            throw new RuntimeException('Checkout session payload is missing.');
        }

        return DB::transaction(function () use ($checkout, $sessionId, $customer) {
            /** @var OrderAddress $shippingAddress */
            $shippingAddress = OrderAddress::query()->create([
                'customer_id'         => data_get($checkout, 'shipping_address.user_id'),
                'last_name'           => data_get($checkout, 'shipping_address.last_name'),
                'first_name'          => data_get($checkout, 'shipping_address.first_name'),
                'street_address'      => data_get($checkout, 'shipping_address.street_address'),
                'street_address_plus' => data_get($checkout, 'shipping_address.street_address_plus'),
                'city'                => data_get($checkout, 'shipping_address.city'),
                'postal_code'         => data_get($checkout, 'shipping_address.postal_code'),
                'phone'               => data_get($checkout, 'shipping_address.phone_number'),
                // @phpstan-ignore-next-line
                'country_name' => Country::query()->find(data_get($checkout, 'shipping_address.country_id'))->name,
            ]);
            /** @var OrderAddress $billingAddress */
            $billingAddress = data_get($checkout, 'same_as_shipping') ? $shippingAddress : OrderAddress::query()->create([
                'customer_id'         => data_get($checkout, 'billing_address.user_id'),
                'last_name'           => data_get($checkout, 'billing_address.last_name'),
                'first_name'          => data_get($checkout, 'billing_address.first_name'),
                'street_address'      => data_get($checkout, 'billing_address.street_address'),
                'street_address_plus' => data_get($checkout, 'billing_address.street_address_plus'),
                'city'                => data_get($checkout, 'billing_address.city'),
                'postal_code'         => data_get($checkout, 'billing_address.postal_code'),
                'phone'               => data_get($checkout, 'billing_address.phone_number'),
                // @phpstan-ignore-next-line
                'country_name' => Country::query()->find(data_get($checkout, 'billing_address.country_id'))->name,
            ]);
            // Totals
            // @phpstan-ignore-next-line
            $subtotal = Number::parseFloat(CartFacade::session($sessionId)->getSubTotal());
            $shippingTotal = Number::parseFloat(data_get($checkout, 'shipping_option.0.price', 0));
            $couponCode = strtoupper((string) data_get($checkout, 'coupon.code'));
            // Validate coupon limits if provided
            $codeRow = null;
            if ($couponCode !== '' && $couponCode !== '0') {
                $codeRow = DB::table('discount_codes')->whereRaw('UPPER(code) = ?', [$couponCode])->first();
                if ($codeRow) {
                    $now = now();
                    if ($codeRow->expires_at && $now->greaterThan($codeRow->expires_at) || $codeRow->max_uses !== null && $codeRow->usage_count >= $codeRow->max_uses) {
                        $codeRow = null;
                        // invalidate
                    }
                }
            }
            $engine = app(\App\Services\Discounts\DiscountEngine::class);
            $result = $engine->evaluate(['currency_code' => current_currency(), 'channel_id' => null, 'user_id' => optional($customer)->id, 'now' => now(), 'code' => $codeRow ? $couponCode : null, 'cart' => ['subtotal' => $subtotal, 'items' => []]]);
            $discountTotal = Number::parseFloat(data_get($result, 'discount_total_amount', 0));
            $shippingDiscount = Number::parseFloat(data_get($result, 'shipping.discount_amount', 0));
            $shippingAmount = max(0.0, $shippingTotal - $shippingDiscount);
            $breakdown = app(PriceCalculator::class)->breakdown($subtotal, $discountTotal, $shippingAmount);
            $grandTotal = $breakdown->total;
            /** @var Order $order */
            $order = Order::query()->create(['number' => generate_number(), 'customer_id' => $customer->id, 'currency_code' => current_currency(), 'shipping_address_id' => $shippingAddress->id, 'billing_address_id' => $billingAddress->id, 'shipping_option_id' => data_get($checkout, 'shipping_option')[0]['id'], 'payment_method_id' => data_get($checkout, 'payment')[0]['id'], 'payment_method' => (string) data_get($checkout, 'payment')[0]['name'], 'subtotal_amount' => round($breakdown->subtotal, 2), 'discount_total_amount' => round($breakdown->discount, 2), 'tax_total_amount' => round($breakdown->tax, 2), 'shipping_total_amount' => round($breakdown->shipping, 2), 'grand_total_amount' => $grandTotal]);
            // Items
            foreach (CartFacade::session($sessionId)->getContent() as $item) {
                OrderItem::query()->create(['order_id' => $order->id, 'quantity' => $item->quantity, 'unit_price_amount' => $item->price, 'name' => $item->name, 'sku' => $item->associatedModel->sku, 'product_id' => $item->associatedModel->id, 'product_type' => $item->associatedModel->getMorphClass()]);
            }
            // Persist redemptions
            foreach ((array) data_get($result, 'applied', []) as $applied) {
                $discountId = (int) ($applied['id'] ?? 0);
                if ($discountId <= 0) {
                    continue;
                }
                // Enforce per-customer limit
                $perCustomer = DB::table('discounts')->where('id', $discountId)->value('per_customer_limit');
                if ($perCustomer) {
                    $used = DB::table('discount_redemptions')->where('discount_id', $discountId)->where('user_id', $customer->id)->count();
                    if ($used >= $perCustomer) {
                        continue;
                        // skip redemption
                    }
                }
                $codeId = null;
                if ($codeRow && (int) $codeRow->discount_id === $discountId) {
                    $codeId = (int) $codeRow->id;
                    // increment usage_count
                    DB::table('discount_codes')->where('id', $codeId)->increment('usage_count');
                }
                DB::table('discount_redemptions')->insert(['discount_id' => $discountId, 'code_id' => $codeId, 'order_id' => $order->id, 'user_id' => $customer->id, 'amount_saved' => round((float) ($applied['amount'] ?? 0), 2), 'currency_code' => current_currency(), 'redeemed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
            }
            // Process payment (stub)
            try {
                $payment = app(\App\Services\Payments\PaymentService::class)->process($order, (array) data_get($checkout, 'payment.0', []));
                $order->payment_status = (string) ($payment['status'] ?? 'pending');
                $existing = safe_json_decode_array((string) ($order->transactions ?? '[]'));
                $existing[] = (array) ($payment['transaction'] ?? []);
                $order->transactions = $existing;
                $order->save();
            } catch (Throwable $e) {
                // ignore payment errors in stub
            }
            // Clear cart
            app(CartLifecycleService::class)->clearAfterCheckout(
                $customer?->id,
                $sessionId,
                $order->payment_status ?? null
            );
            // Queue order confirmation email with user's preferred locale
            try {
                $mailable = new OrderPlaced($order);
                if (! empty($customer->preferred_locale)) {
                    $mailable->locale($customer->preferred_locale);
                }
                Mail::to($customer->email)->queue($mailable);
            } catch (Throwable) {
                // swallow mail errors to not block checkout
            }

            return $order;
        });
    }
}
