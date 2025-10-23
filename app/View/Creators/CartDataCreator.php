<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Services\Pricing\PriceCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * CartDataCreator
 *
 * View Creator that provides cart data to views.
 * This includes cart items, totals, and cart-related information.
 */
final class CartDataCreator
{
    public function __construct(private readonly PriceCalculator $priceCalculator) {}

    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        $userIdentifier = Auth::id();
        $userId = is_numeric($userIdentifier) ? (int) $userIdentifier : null;
        $summary = $this->cartService->getSummary($userId, Session::getId());

        $view->with([
            'cart' => $summary,
            'cartCount' => $summary['count'],
            'cartTotal' => $summary['total'],
            'cartSubtotal' => $summary['subtotal'],
            'cartTax' => $summary['tax'],
            'cartShipping' => $summary['shipping'],
            'cartDiscount' => $summary['discount'],
            'cartItems' => $summary['items'],
            'hasCartItems' => $summary['count'] > 0,
            'isCartEmpty' => $summary['count'] === 0,
        ]);
    }

    /**
     * Get cart data from session.
     */
    private function getCartData(): array
    {
        /** @var array<int, array<string, mixed>> $cart */
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
        $count = 0;

        foreach ($cart as $item) {
            $priceRaw = $item['price'] ?? 0.0;
            $quantityRaw = $item['quantity'] ?? 0;
            $price = is_numeric($priceRaw) ? (float) $priceRaw : 0.0;
            $quantity = is_numeric($quantityRaw) ? (int) $quantityRaw : 0;
            $lineTotal = $this->priceCalculator->round($price * $quantity);
            $count += $quantity;

            $items[] = [
                'id' => $item['id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'variant_id' => $item['variant_id'] ?? null,
                'name' => $item['name'] ?? '',
                'price' => $this->priceCalculator->round($price),
                'quantity' => $quantity,
                'total' => $lineTotal,
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
