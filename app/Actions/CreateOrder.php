<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\OrderPlaced;
use App\Models\Country;
use App\Models\Order;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Number;

/**
 * CreateOrder
 *
 * Action class for CreateOrder single-purpose operations with validation, error handling, and result reporting.
 */
class CreateOrder
{
    /**
     * Handle the job, event, or request processing.
     */
    public function handle(): Order
    {
        $checkout = session()->get('checkout');
        $sessionId = session()->getId();
        $customer = Auth::user();

        return DB::transaction(function () use ($checkout, $sessionId, $customer) {
            /** @var OrderAddress $shippingAddress */
            $shippingAddress = OrderAddress::query()->create([
                'customer_id' => data_get($checkout, 'shipping_address.user_id'),
                'last_name' => data_get($checkout, 'shipping_address.last_name'),
                'first_name' => data_get($checkout, 'shipping_address.first_name'),
                'street_address' => data_get($checkout, 'shipping_address.street_address'),
                'street_address_plus' => data_get($checkout, 'shipping_address.street_address_plus'),
                'city' => data_get($checkout, 'shipping_address.city'),
                'postal_code' => data_get($checkout, 'shipping_address.postal_code'),
                'phone' => data_get($checkout, 'shipping_address.phone_number'),
                // @phpstan-ignore-next-line
                'country_name' => Country::query()->find(data_get($checkout, 'shipping_address.country_id'))->name,
            ]);
            /** @var OrderAddress $billingAddress */
            $billingAddress = ! data_get($checkout, 'same_as_shipping') ? OrderAddress::query()->create([
                'customer_id' => data_get($checkout, 'billing_address.user_id'),
                'last_name' => data_get($checkout, 'billing_address.last_name'),
                'first_name' => data_get($checkout, 'billing_address.first_name'),
                'street_address' => data_get($checkout, 'billing_address.street_address'),
                'street_address_plus' => data_get($checkout, 'billing_address.street_address_plus'),
                'city' => data_get($checkout, 'billing_address.city'),
                'postal_code' => data_get($checkout, 'billing_address.postal_code'),
                'phone' => data_get($checkout, 'billing_address.phone_number'),
                // @phpstan-ignore-next-line
                'country_name' => Country::query()->find(data_get($checkout, 'billing_address.country_id'))->name,
            ]) : $shippingAddress;
            // Totals
            // @phpstan-ignore-next-line
            $subtotal = Number::parseFloat(CartFacade::session($sessionId)->getSubTotal());
            $shippingTotal = Number::parseFloat(data_get($checkout, 'shipping_option.0.price', 0));
            $couponCode = strtoupper((string) data_get($checkout, 'coupon.code'));
            // Validate coupon limits if provided
            $codeRow = null;
            if ($couponCode) {
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
            $taxTotal = app(\App\Services\Taxes\TaxCalculator::class)->compute(max(0.0, $subtotal - (float) $discountTotal), null);
            $grandTotal = max(0, round($subtotal - $discountTotal + $shippingTotal + $taxTotal, 2));
            /** @var Order $order */
            $order = Order::query()->create(['number' => generate_number(), 'customer_id' => $customer->id, 'currency_code' => current_currency(), 'shipping_address_id' => $shippingAddress->id, 'billing_address_id' => $billingAddress->id, 'shipping_option_id' => data_get($checkout, 'shipping_option')[0]['id'], 'payment_method_id' => data_get($checkout, 'payment')[0]['id'], 'payment_method' => (string) data_get($checkout, 'payment')[0]['name'], 'subtotal_amount' => round($subtotal, 2), 'discount_total_amount' => round($discountTotal, 2), 'tax_total_amount' => round($taxTotal, 2), 'shipping_total_amount' => round($shippingTotal, 2), 'grand_total_amount' => $grandTotal]);
            // Items
            // @phpstan-ignore-next-line
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
                $existing = (array) json_decode((string) $order->transactions ?? '[]', true);
                $existing[] = (array) ($payment['transaction'] ?? []);
                $order->transactions = $existing;
                $order->save();
            } catch (\Throwable $e) {
                // ignore payment errors in stub
            }
            // Clear cart
            CartFacade::session($sessionId)->clear();
            // @phpstan-ignore-line
            // Queue order confirmation email with user's preferred locale
            try {
                $mailable = new OrderPlaced($order);
                if (! empty($customer->preferred_locale)) {
                    $mailable->locale($customer->preferred_locale);
                }
                Mail::to($customer->email)->queue($mailable);
            } catch (\Throwable $e) {
                // swallow mail errors to not block checkout
            }

            return $order;
        });
    }
}
