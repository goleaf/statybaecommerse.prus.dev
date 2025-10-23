<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use Illuminate\Contracts\Session\Session;

final class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(private readonly Session $session)
    {
    }

    public function getCount(?int $userId, ?string $sessionId): int
    {
        $databaseCount = 0;

        if ($userId !== null && $sessionId !== null) {
            $databaseCount = (int) CartItem::query()
                ->where(function ($query) use ($userId, $sessionId) {
                    $query->where('user_id', $userId)
                        ->orWhere('session_id', $sessionId);
                })
                ->sum('quantity');
        } elseif ($userId !== null) {
            $databaseCount = (int) CartItem::query()
                ->where('user_id', $userId)
                ->sum('quantity');
        } elseif ($sessionId !== null) {
            $databaseCount = (int) CartItem::query()
                ->where('session_id', $sessionId)
                ->sum('quantity');
        }

        if ($databaseCount > 0) {
            return $databaseCount;
        }

        return $this->getSessionCount();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSessionItems(): array
    {
        $items = $this->session->get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? $key);

            if ($productId <= 0) {
                continue;
            }

            $normalized[$productId] = array_merge($item, [
                'product_id' => $productId,
                'quantity' => (int) ($item['quantity'] ?? 0),
            ]);
        }

        return $normalized;
    }

    public function getSessionCount(): int
    {
        return array_sum(array_map(
            static fn (array $item): int => (int) ($item['quantity'] ?? 0),
            $this->getSessionItems()
        ));
    }
}
