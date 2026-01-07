<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Security;

use Illuminate\Cache\RateLimiter as LaravelRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Rate limiter for version compatibility operations
 *
 * Prevents abuse and DoS attacks by limiting the number of transformation
 * operations per user/IP within specified time windows.
 */
final class RateLimiter
{
    private readonly int $maxAttempts;

    private readonly int $decayMinutes;

    private readonly bool $enableIpBasedLimiting;

    private readonly bool $enableUserBasedLimiting;

    public function __construct(
        private readonly LaravelRateLimiter $rateLimiter,
        array $config = []
    ) {
        $this->maxAttempts = $config['max_attempts'] ?? 60; // 60 per hour by default
        $this->decayMinutes = $config['decay_minutes'] ?? 60; // 1 hour window
        $this->enableIpBasedLimiting = $config['enable_ip_limiting'] ?? true;
        $this->enableUserBasedLimiting = $config['enable_user_limiting'] ?? true;
    }

    /**
     * Check if the current request is rate limited
     *
     * @throws RuntimeException When rate limit is exceeded
     */
    public function checkRateLimit(?Request $request = null): void
    {
        $request = $request ?? request();

        if ($this->enableIpBasedLimiting) {
            $this->checkIpRateLimit($request);
        }

        if ($this->enableUserBasedLimiting && $request->user()) {
            $this->checkUserRateLimit($request);
        }
    }

    /**
     * Record a successful operation
     */
    public function recordAttempt(?Request $request = null): void
    {
        $request = $request ?? request();

        if ($this->enableIpBasedLimiting) {
            $this->rateLimiter->hit($this->getIpKey($request), $this->decayMinutes * 60);
        }

        if ($this->enableUserBasedLimiting && $request->user()) {
            $this->rateLimiter->hit($this->getUserKey($request), $this->decayMinutes * 60);
        }
    }

    /**
     * Get remaining attempts for current request
     */
    public function getRemainingAttempts(?Request $request = null): int
    {
        $request = $request ?? request();

        $ipRemaining = $this->enableIpBasedLimiting
            ? $this->rateLimiter->remaining($this->getIpKey($request), $this->maxAttempts)
            : $this->maxAttempts;

        $userRemaining = ($this->enableUserBasedLimiting && $request->user())
            ? $this->rateLimiter->remaining($this->getUserKey($request), $this->maxAttempts)
            : $this->maxAttempts;

        return min($ipRemaining, $userRemaining);
    }

    /**
     * Clear rate limit for current request (admin override)
     */
    public function clearRateLimit(?Request $request = null): void
    {
        $request = $request ?? request();

        if ($this->enableIpBasedLimiting) {
            $this->rateLimiter->clear($this->getIpKey($request));
        }

        if ($this->enableUserBasedLimiting && $request->user()) {
            $this->rateLimiter->clear($this->getUserKey($request));
        }

        Log::channel('security')->info('Rate limit cleared', [
            'ip'      => $request->ip(),
            'user_id' => $request->user()?->id,
        ]);
    }

    /**
     * Check IP-based rate limit
     */
    private function checkIpRateLimit(Request $request): void
    {
        $key = $this->getIpKey($request);

        if ($this->rateLimiter->tooManyAttempts($key, $this->maxAttempts)) {
            $retryAfter = $this->rateLimiter->availableIn($key);

            Log::channel('security')->warning('IP rate limit exceeded', [
                'ip'          => $request->ip(),
                'key'         => $key,
                'retry_after' => $retryAfter,
                'user_agent'  => $request->userAgent(),
            ]);

            throw new RuntimeException(
                "Too many transformation attempts. Try again in {$retryAfter} seconds."
            );
        }
    }

    /**
     * Check user-based rate limit
     */
    private function checkUserRateLimit(Request $request): void
    {
        $key = $this->getUserKey($request);

        if ($this->rateLimiter->tooManyAttempts($key, $this->maxAttempts)) {
            $retryAfter = $this->rateLimiter->availableIn($key);

            Log::channel('security')->warning('User rate limit exceeded', [
                'user_id'     => $request->user()->id,
                'key'         => $key,
                'retry_after' => $retryAfter,
            ]);

            throw new RuntimeException(
                "Too many transformation attempts. Try again in {$retryAfter} seconds."
            );
        }
    }

    /**
     * Generate IP-based rate limit key
     */
    private function getIpKey(Request $request): string
    {
        return 'version_compat_ip:' . $request->ip();
    }

    /**
     * Generate user-based rate limit key
     */
    private function getUserKey(Request $request): string
    {
        return 'version_compat_user:' . $request->user()->id;
    }
}
