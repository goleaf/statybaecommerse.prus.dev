<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Services\Cart\CartService;
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
    public function __construct(private readonly CartService $cartService) {}

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
}
