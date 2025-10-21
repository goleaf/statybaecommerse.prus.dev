<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Services\Pricing\PriceCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;

/**
 * CartDataCreator
 *
 * View Creator that provides cart data to views.
 * This includes cart items, totals, and cart-related information.
 */
final class CartDataCreator
{
    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        $cart = $this->getCartData();

        $view->with([
            'cart' => $cart,
            'cartCount' => $cart['count'],
            'cartTotal' => $cart['total'],
            'cartSubtotal' => $cart['subtotal'],
            'cartTax' => $cart['tax'],
            'cartShipping' => $cart['shipping'],
            'cartDiscount' => $cart['discount'],
            'cartItems' => $cart['items'],
            'hasCartItems' => $cart['count'] > 0,
            'isCartEmpty' => $cart['count'] === 0,
        ]);
    }

    /**
     * Get cart data from session.
     */
    private function getCartData(): array
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return [
                'items' => [],
                'count' => 0,
                'subtotal' => 0,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => 0,
            ];
        }

        $items = [];
        $subtotal = 0;
        $count = 0;

        foreach ($cart as $item) {
            $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            $subtotal += $itemTotal;
            $count += $item['quantity'] ?? 0;

            $items[] = [
                'id' => $item['id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'variant_id' => $item['variant_id'] ?? null,
                'name' => $item['name'] ?? '',
                'price' => $item['price'] ?? 0,
                'quantity' => $item['quantity'] ?? 0,
                'total' => $itemTotal,
                'image' => $item['image'] ?? null,
                'attributes' => $item['attributes'] ?? [],
            ];
        }

        $discount = (float) Session::get('cart_discount', 0);
        $breakdown = app(PriceCalculator::class)->breakdown($subtotal, $discount);

        return [
            'items' => $items,
            'count' => $count,
            'subtotal' => $breakdown->subtotal,
            'tax' => $breakdown->tax,
            'shipping' => $breakdown->shipping,
            'discount' => $breakdown->discount,
            'total' => $breakdown->total,
        ];
    }
}
