<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Coupon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CouponApplied
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param array<string, mixed> $pricing
     * @param array<string, mixed> $context
     */
    public function __construct(
        public Coupon $coupon,
        public array $pricing,
        public array $context,
    ) {}
}
