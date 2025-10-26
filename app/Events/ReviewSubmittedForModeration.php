<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Review;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ReviewSubmittedForModeration
 *
 * Event dispatched whenever a shopper submits a new product review so the
 * moderation pipeline can run asynchronously in the background.
 */
final class ReviewSubmittedForModeration
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Review $review,
    ) {
        // No-op constructor keeps the payload strongly typed for listeners.
    }
}
