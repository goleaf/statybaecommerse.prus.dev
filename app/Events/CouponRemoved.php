<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CouponRemoved
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param array<string, mixed> $pricing
     * @param array<string, mixed> $context
     */
    public function __construct(
        public ?string $code,
        public array $pricing,
        public array $context,
    ) {}
}
