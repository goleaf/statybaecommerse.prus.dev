<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Enums\PaymentStatus;
use App\Models\CartItem;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * CartLifecycleService
 *
 * Provides a single place to manage cart cleanup when checkout succeeds or is
 * abandoned. The service coordinates between session based carts,
 * database-backed cart items and optional third-party cart storage.
 */
final class CartLifecycleService
{
    private const SESSION_CART_KEY = 'cart';

    public function __construct(private readonly Session $session) {}

    /**
     * Clear cart state after a successful checkout.
     */
    public function clearAfterCheckout(?int $userId, ?string $sessionId, PaymentStatus|string|null $paymentStatus = null): void
    {
        if ($this->shouldRetainForPartialPayment($paymentStatus)) {
            return;
        }

        $this->clearTargets($userId, $sessionId);
    }

    /**
     * Clear cart state when a checkout flow is abandoned.
     */
    public function clearForAbandonedCheckout(?int $userId, ?string $sessionId): void
    {
        $this->clearTargets($userId, $sessionId);
    }

    private function shouldRetainForPartialPayment(PaymentStatus|string|null $paymentStatus): bool
    {
        if ($paymentStatus === null) {
            return false;
        }

        $value = $paymentStatus instanceof PaymentStatus ? $paymentStatus->value : $paymentStatus;

        return in_array(strtolower($value), [
            'partial',
            'partially_paid',
            'partially_authorized',
            'requires_action',
            'requires_payment',
            'requires_payment_method',
        ], true);
    }

    private function clearTargets(?int $userId, ?string $sessionId): void
    {
        $items = collect();

        if ($userId !== null) {
            $items = $items->merge(
                $this->baseQuery()
                    ->where('user_id', $userId)
                    ->get(['id', 'session_id'])
            );
        }

        if ($sessionId !== null) {
            $items = $items->merge(
                $this->baseQuery()
                    ->where('session_id', $sessionId)
                    ->get(['id', 'session_id'])
            );
        }

        /** @var Collection<int, array{id:int, session_id:?string}> $items */
        $items = $items->unique('id')->values();

        if ($items->isNotEmpty()) {
            $this->baseQuery()->whereIn('id', $items->pluck('id'))->forceDelete();
        }

        if ($userId !== null) {
            $this->baseQuery()
                ->where('user_id', $userId)
                ->whereNull('session_id')
                ->forceDelete();
        }

        $sessionKeys = $items->pluck('session_id')
            ->filter()
            ->map(static fn ($value): string => (string) $value)
            ->values();

        if ($sessionId !== null) {
            $sessionKeys->prepend($sessionId);
        }

        if ($sessionKeys->isEmpty() && $sessionId === null && $userId !== null) {
            $sessionKeys->push($this->session->getId());
        }

        $this->clearSessions($sessionKeys->unique()->values());
    }

    /**
     * @param  Collection<int, string>  $sessionKeys
     */
    private function clearSessions(Collection $sessionKeys): void
    {
        foreach ($sessionKeys as $key) {
            if ($key === '') {
                continue;
            }

            if ($key === $this->session->getId()) {
                $this->session->forget(self::SESSION_CART_KEY);
            }

            $this->clearFacadeSession($key);
        }

        if ($sessionKeys->doesntContain($this->session->getId())) {
            $this->session->forget(self::SESSION_CART_KEY);
        }
    }

    private function clearFacadeSession(string $sessionKey): void
    {
        if (! class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            return;
        }

        try {
            \Darryldecode\Cart\Facades\CartFacade::session($sessionKey)->clear();
        } catch (\Throwable) {
            // Swallow errors from optional cart integrations.
        }
    }

    private function baseQuery(): Builder
    {
        return CartItem::withoutGlobalScopes();
    }
}
