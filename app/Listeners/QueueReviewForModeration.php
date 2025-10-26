<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReviewSubmittedForModeration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * QueueReviewForModeration
 *
 * Listener responsible for handing off freshly submitted reviews to the
 * moderation queue so expensive analysis can run out-of-band.
 */
final class QueueReviewForModeration implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ReviewSubmittedForModeration $event): void
    {
        // In a real deployment this would dispatch a dedicated job or call an
        // external moderation service. For now we simply log the intent so the
        // queue contract remains satisfied and easily traceable in tests.
        Log::info('Review queued for moderation.', [
            'review_id' => $event->review->getKey(),
            'product_id' => $event->review->product_id,
        ]);
    }
}
