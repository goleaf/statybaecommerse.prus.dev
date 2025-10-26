<?php

declare(strict_types=1);

namespace App\Support\Security;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Persist non-sensitive metadata for successful authentication events.
 */
final class LoginRecorder
{
    /**
     * Record the latest login timestamp and anonymised device fingerprints.
     */
    public function record(User $user, Request $request): void
    {
        $ip = (string) $request->ip();
        $userAgent = (string) $request->userAgent();

        $normalizedIp = $ip !== '' ? $ip : 'unknown';
        $normalizedAgent = $userAgent !== '' ? $userAgent : 'unknown';

        $ipHash = $this->hashIdentifier($normalizedIp);
        $deviceHash = $this->hashIdentifier($normalizedAgent);

        $preferences = $user->preferences;
        if (! is_array($preferences)) {
            $preferences = [];
        }

        $preferences['last_login'] = [
            'device_hash' => $deviceHash,
            'ip_hash' => $ipHash,
            'recorded_at' => now()->toIso8601String(),
        ];

        $user->forceFill([
            'last_login_at' => now(),
            // Persist the hashed representation instead of the raw IP address.
            'last_login_ip' => $ipHash,
            'login_count' => (int) $user->login_count + 1,
            'preferences' => $preferences,
        ]);

        $user->save();
    }

    /**
     * Hash identifiers with the application key to avoid leaking raw values at rest.
     */
    private function hashIdentifier(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }
}
