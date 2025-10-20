<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): JsonResponse
    {
        // TODO: Implement cart listing
        return response()->json(['message' => 'Cart listing not implemented yet']);
    }

    public function add(Request $request): JsonResponse
    {
        // TODO: Implement add to cart
        return response()->json(['message' => 'Add to cart not implemented yet']);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        // TODO: Implement cart item update
        return response()->json(['message' => 'Cart update not implemented yet', 'id' => $id]);
    }

    public function remove(string $id): JsonResponse
    {
        // TODO: Implement cart item removal
        return response()->json(['message' => 'Cart item removal not implemented yet', 'id' => $id]);
    }

    public function clear(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $userIdentifier = $request->user()?->getAuthIdentifier();
        $userId = is_numeric($userIdentifier) ? (int) $userIdentifier : null;

        $fallbackSessionId = $request->session()->get('cart_session_id');
        $fallbackSessionId = is_string($fallbackSessionId) ? $fallbackSessionId : null;

        $this->cartService->clear($userId, $sessionId, $fallbackSessionId);
        $summary = $this->cartService->getSummary($userId, $sessionId);

        return response()->json([
            'success' => true,
            'message' => __('Cart cleared successfully.'),
            'cart' => $summary,
        ]);
    }
}
