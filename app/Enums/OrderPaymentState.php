<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * OrderPaymentState enumerates the payment lifecycle used by webhook
 * processing so we can enforce a predictable state machine for each order.
 */
enum OrderPaymentState: string
{
    case CREATED = 'created';
    case PAID = 'paid';
    case FULFILLED = 'fulfilled';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case REFUNDED = 'refunded';

    /**
     * Determine whether the state machine allows transitioning to the given
     * target state while remaining tolerant of idempotent updates.
     */
    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            // Accept idempotent events so replays do not raise exceptions.
            return true;
        }

        return match ($this) {
            self::CREATED => $target === self::PAID,
            self::PAID => in_array($target, [self::FULFILLED, self::PARTIALLY_REFUNDED, self::REFUNDED], true),
            self::FULFILLED => in_array($target, [self::PARTIALLY_REFUNDED, self::REFUNDED], true),
            self::PARTIALLY_REFUNDED => $target === self::REFUNDED,
            self::REFUNDED => false,
        };
    }
}
