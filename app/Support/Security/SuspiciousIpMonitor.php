<?php

declare(strict_types=1);

namespace App\Support\Security;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

final class SuspiciousIpMonitor
{
    public function record(string $ip, string $reason, array $context = []): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $ip = $this->normalizeIp($ip);
        $threshold = $this->threshold();
        $decay = $this->decaySeconds();
        $key = $this->key($ip, $reason);

        $attempts = RateLimiter::hit($key, $decay);

        if ($attempts < $threshold) {
            return;
        }

        if ($attempts === $threshold) {
            Log::channel('security')->warning('Suspicious activity detected.', array_merge([
                'ip'            => $ip,
                'reason'        => $reason,
                'attempts'      => $attempts,
                'threshold'     => $threshold,
                'decay_seconds' => $decay,
            ], $context));

            return;
        }

        if ($attempts % $threshold === 0) {
            Log::channel('security')->notice('Suspicious activity persists.', array_merge([
                'ip'            => $ip,
                'reason'        => $reason,
                'attempts'      => $attempts,
                'threshold'     => $threshold,
                'decay_seconds' => $decay,
            ], $context));
        }
    }

    public function reset(string $ip, string $reason): void
    {
        RateLimiter::clear($this->key($this->normalizeIp($ip), $reason));
    }

    private function key(string $ip, string $reason): string
    {
        return Str::of('security|ip-monitor|')
            ->append($reason)
            ->append('|')
            ->append($ip)
            ->value();
    }

    private function threshold(): int
    {
        return max(1, (int) data_get(config('security.monitoring.suspicious_ip'), 'threshold', 10));
    }

    private function decaySeconds(): int
    {
        return max(60, (int) data_get(config('security.monitoring.suspicious_ip'), 'decay_seconds', 900));
    }

    private function isEnabled(): bool
    {
        return (bool) data_get(config('security.monitoring.suspicious_ip'), 'enabled', true);
    }

    private function normalizeIp(string $ip): string
    {
        $normalized = trim($ip);

        return $normalized !== '' ? $normalized : 'unknown';
    }
}
