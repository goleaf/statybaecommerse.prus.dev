<?php

declare(strict_types=1);

namespace App\View\Creators;

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

        // Calculate tax (simplified - in real app this would be more complex)
        $taxRate = config('shared.tax.default_rate', 0.21); // 21% VAT
        $tax = $subtotal * $taxRate;
        
        // Calculate shipping (simplified)
        $shipping = $subtotal > 50 ? 0 : 5.99; // Free shipping over €50
        
        // Get discount from session
        $discount = Session::get('cart_discount', 0);
        
        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'items' => $items,
            'count' => $count,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
        ];
    }
}
