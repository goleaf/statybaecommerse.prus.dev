<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class NotificationRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = 'default'): Response
    {
        $key = $this->resolveRequestSignature($request, $action);

        $maxAttempts = $this->getMaxAttempts($action);
        $decayMinutes = $this->getDecayMinutes($action);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Too many notification requests. Please try again in '.$seconds.' seconds.',
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $maxAttempts));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);

        return $response;
    }

    /**
     * Resolve the request signature for rate limiting
     */
    private function resolveRequestSignature(Request $request, string $action): string
    {
        $user = $request->user();
        $userId = $user ? $user->id : $request->ip();

        return "notifications:{$action}:{$userId}";
    }

    /**
     * Get the maximum number of attempts for the given action
     */
    private function getMaxAttempts(string $action): int
    {
        return match ($action) {
            'create' => 10,      // 10 notifications per minute
            'mark_read' => 60,   // 60 mark as read per minute
            'mark_unread' => 60, // 60 mark as unread per minute
            'delete' => 30,      // 30 deletes per minute
            'search' => 100,     // 100 searches per minute
            'stats' => 200,      // 200 stats requests per minute
            default => 100,      // 100 requests per minute
        };
    }

    /**
     * Get the decay minutes for the given action
     */
    private function getDecayMinutes(string $action): int
    {
        return match ($action) {
            'create' => 1,       // 1 minute
            'mark_read' => 1,    // 1 minute
            'mark_unread' => 1,  // 1 minute
            'delete' => 1,       // 1 minute
            'search' => 1,       // 1 minute
            'stats' => 1,        // 1 minute
            default => 1,        // 1 minute
        };
    }
}
