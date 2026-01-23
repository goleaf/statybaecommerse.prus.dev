<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    /**
     * Handle an incoming request with enhanced security and tenant isolation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limit impersonation attempts per IP
        $key = 'impersonation_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            Log::warning('Impersonation rate limit exceeded', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['error' => 'Too many impersonation attempts'], 429);
        }

        if ($this->hasValidImpersonationSession()) {
            RateLimiter::hit($key, 300); // 5 minute decay
            $this->handleImpersonation($request);
        }

        return $next($request);
    }

    /**
     * Check if there's a valid impersonation session.
     */
    private function hasValidImpersonationSession(): bool
    {
        if (! session()->has('impersonating')) {
            return false;
        }

        $data = session('impersonating');

        return is_array($data)
            && isset($data['user'], $data['original_user_id'], $data['started_at'])
            && $data['user'] instanceof User
            && is_int($data['original_user_id'])
            && $data['started_at'] instanceof DateTimeInterface;
    }

    /**
     * Handle the impersonation logic.
     */
    private function handleImpersonation(Request $request): void
    {
        $impersonatingData = session('impersonating');

        // Validate session hasn't expired (24 hours max)
        if ($this->isImpersonationExpired($impersonatingData['started_at'])) {
            $this->endImpersonation();

            return;
        }

        // Authenticate as the impersonated user
        Auth::login($impersonatingData['user']);

        // Share impersonation data with views only when needed
        if ($request->expectsJson() === false) {
            View::share('impersonating', [
                'user'             => $impersonatingData['user'],
                'original_user_id' => $impersonatingData['original_user_id'],
                'started_at'       => $impersonatingData['started_at'],
            ]);
        }

        // Log impersonation activity for audit trail
        Log::info('User impersonation active', [
            'impersonated_user_id' => $impersonatingData['user']->id,
            'original_user_id'     => $impersonatingData['original_user_id'],
            'ip_address'           => $request->ip(),
            'user_agent'           => $request->userAgent(),
        ]);
    }

    /**
     * Check if impersonation session has expired.
     */
    private function isImpersonationExpired(DateTimeInterface $startedAt): bool
    {
        return $startedAt->diffInHours(now()) > 24;
    }

    /**
     * End the impersonation session.
     */
    private function endImpersonation(): void
    {
        session()->forget('impersonating');
        Auth::logout();

        Log::info('Impersonation session ended due to expiration');
    }
}
