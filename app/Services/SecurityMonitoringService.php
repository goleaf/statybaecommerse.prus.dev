<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Security monitoring and threat detection service.
 */
final class SecurityMonitoringService
{
    private const SUSPICIOUS_IP_CACHE_KEY = 'security:suspicious_ips';

    private const BLOCKED_IP_CACHE_KEY = 'security:blocked_ips';

    private const THREAT_COUNTER_CACHE_KEY = 'security:threat_count';

    private const THREAT_TYPE_COUNTER_CACHE_KEY = 'security:threat_type_counts';

    private const THREATS_DETECTED_TODAY_KEY = 'security:threats_detected_today';

    private const SUSPICIOUS_USER_AGENTS_KEY = 'security:suspicious_user_agents';

    private const FAILED_LOGIN_ATTEMPTS_KEY = 'security:failed_login_attempts';

    private readonly int $suspiciousThreshold;

    private readonly int $blockThreshold;

    private readonly int $decayMinutes;

    public function __construct(CacheService|int|null $cacheOrSuspiciousThreshold = null, ?int $blockThreshold = null, ?int $decayMinutes = null)
    {
        $this->suspiciousThreshold = is_int($cacheOrSuspiciousThreshold)
            ? $cacheOrSuspiciousThreshold
            : 3;
        $this->blockThreshold = $blockThreshold ?? 5;
        $this->decayMinutes = $decayMinutes ?? 15;
    }

    /**
     * Monitor request for security threats.
     *
     * @return list<string>
     */
    public function monitorRequest(Request $request): array
    {
        $ip = (string) ($request->ip() ?: 'unknown');
        $threats = $this->analyzeRequest($request);

        if ($threats !== []) {
            $this->updateThreatCounters($ip, $threats);
            $this->trackSuspiciousActivity($ip, $threats);

            foreach ($threats as $threatType) {
                $this->logThreat($request, $ip, $threatType);
            }
        }

        if (! $this->isIpBlocked($ip) && $this->shouldBlockIp($ip)) {
            $this->blockIp($ip, 24 * 60, 'Automatic block after repeated threats');
            $threats[] = 'ip_auto_blocked';
        }

        return $threats;
    }

    /**
     * @return list<string>
     */
    private function analyzeRequest(Request $request): array
    {
        $threats = [];

        if ($this->detectSqlInjection($request)) {
            $threats[] = 'sql_injection';
        }

        if ($this->detectXss($request)) {
            $threats[] = 'xss_attempts';
        }

        if ($this->detectPathTraversal($request)) {
            $threats[] = 'path_traversal';
        }

        if ($this->detectCommandInjection($request)) {
            $threats[] = 'command_injection';
        }

        if ($this->detectSuspiciousUserAgent($request)) {
            $threats[] = 'suspicious_user_agent';
            $this->rememberSuspiciousUserAgent((string) ($request->userAgent() ?: ''));
        }

        return array_values(array_unique($threats));
    }

    private function detectSqlInjection(Request $request): bool
    {
        $patterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION)\b/i',
            '/\b(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/\bUNION\b.*\bSELECT\b/i',
            '/\b(INFORMATION_SCHEMA|SYSOBJECTS|SYSCOLUMNS)\b/i',
        ];

        return $this->checkPatterns($request, $patterns);
    }

    private function detectXss(Request $request): bool
    {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/expression\s*\(/i',
        ];

        return $this->checkPatterns($request, $patterns);
    }

    private function detectPathTraversal(Request $request): bool
    {
        $patterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/i',
            '/%2e%2e%5c/i',
        ];

        return $this->checkPatterns($request, $patterns);
    }

    private function detectCommandInjection(Request $request): bool
    {
        $patterns = [
            '/(\||;|&|\$\(|`|>|<)/i',
            '/\b(cat|ls|pwd|id|whoami|uname|ps|netstat|ifconfig|ping|wget|curl|nc|telnet|ssh|ftp)\b/i',
        ];

        return $this->checkPatterns($request, $patterns);
    }

    private function detectSuspiciousUserAgent(Request $request): bool
    {
        $userAgent = strtolower((string) ($request->userAgent() ?? ''));

        foreach ([
            'sqlmap',
            'nikto',
            'nessus',
            'openvas',
            'nmap',
            'masscan',
            'zap',
            'burp',
            'w3af',
            'havij',
        ] as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $patterns
     */
    private function checkPatterns(Request $request, array $patterns): bool
    {
        $allInput = array_merge(
            $request->query(),
            $request->request->all(),
            [$request->getPathInfo(), (string) ($request->userAgent() ?? '')]
        );

        foreach ($allInput as $value) {
            if (! is_string($value)) {
                continue;
            }

            $candidates = array_values(array_unique([
                $value,
                urldecode($value),
                html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            ]));

            foreach ($candidates as $candidate) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $candidate) === 1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param list<string> $threats
     */
    private function updateThreatCounters(string $ip, array $threats): void
    {
        $ipKey = self::THREAT_COUNTER_CACHE_KEY . ':' . $ip;
        $current = (int) Cache::get($ipKey, 0);
        $newCount = $current + count($threats);

        Cache::put($ipKey, $newCount, now()->addMinutes($this->decayMinutes));

        if ($newCount >= $this->suspiciousThreshold) {
            $suspiciousIps = Cache::get(self::SUSPICIOUS_IP_CACHE_KEY, []);
            $suspiciousIps[$ip] = now()->timestamp;
            Cache::put(self::SUSPICIOUS_IP_CACHE_KEY, $suspiciousIps, now()->addHours(24));
        }

        $dailyCount = (int) Cache::get(self::THREATS_DETECTED_TODAY_KEY, 0);
        Cache::put(self::THREATS_DETECTED_TODAY_KEY, $dailyCount + count($threats), now()->endOfDay());

        $typeCounters = Cache::get(self::THREAT_TYPE_COUNTER_CACHE_KEY, []);
        foreach ($threats as $threat) {
            $typeCounters[$threat] = ((int) ($typeCounters[$threat] ?? 0)) + 1;
        }
        Cache::put(self::THREAT_TYPE_COUNTER_CACHE_KEY, $typeCounters, now()->endOfDay());
    }

    /**
     * @param list<string> $threats
     */
    private function trackSuspiciousActivity(string $ip, array $threats): void
    {
        $key = "security:suspicious_activity:{$ip}";
        $activities = Cache::get($key, []);

        $activities[] = [
            'threats'     => $threats,
            'recorded_at' => now()->toIso8601String(),
        ];

        if (count($activities) > 10) {
            $activities = array_slice($activities, -10);
        }

        Cache::put($key, $activities, now()->addHours(24));
    }

    private function rememberSuspiciousUserAgent(string $userAgent): void
    {
        if ($userAgent === '') {
            return;
        }

        $agents = Cache::get(self::SUSPICIOUS_USER_AGENTS_KEY, []);
        $normalized = trim($userAgent);

        if ($normalized !== '' && ! in_array($normalized, $agents, true)) {
            $agents[] = $normalized;
        }

        Cache::put(self::SUSPICIOUS_USER_AGENTS_KEY, array_slice($agents, -20), now()->endOfDay());
    }

    private function logThreat(Request $request, string $ip, string $threatType): void
    {
        $query = http_build_query($request->query(), '', '&', PHP_QUERY_RFC1738);
        $url = $request->url() . ($query !== '' ? '?' . $query : '');

        Log::warning('Security threat detected', [
            'threat_type'  => $threatType,
            'ip_address'   => $ip,
            'url'          => $url,
            'method'       => $request->method(),
            'user_agent'   => (string) ($request->userAgent() ?? ''),
            'request_id'   => (string) ($request->header('X-Request-ID') ?? ''),
            'request_data' => $this->sanitizeRequestData($request),
        ]);
    }

    /**
     * @return array{query: array<string, mixed>, input: array<string, mixed>}
     */
    private function sanitizeRequestData(Request $request): array
    {
        $input = $request->request->all();
        array_walk_recursive($input, static function (&$value, $key): void {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, ['password', 'password_confirmation', 'current_password', 'token', 'secret', 'api_key'], true)) {
                $value = '[REDACTED]';
            }
        });

        return [
            'query' => $request->query(),
            'input' => $input,
        ];
    }

    public function shouldBlockIp(string $ip): bool
    {
        if ($this->isIpBlocked($ip)) {
            return true;
        }

        $threatCount = (int) Cache::get(self::THREAT_COUNTER_CACHE_KEY . ':' . $ip, 0);

        return $threatCount >= $this->blockThreshold;
    }

    public function blockIp(string $ip, int $durationMinutes = 60, ?string $reason = null): void
    {
        $cacheKey = self::BLOCKED_IP_CACHE_KEY . ':' . $ip;

        if ($durationMinutes <= 0) {
            Cache::forget($cacheKey);
        } else {
            Cache::put($cacheKey, true, now()->addMinutes($durationMinutes));
        }

        $blockedIps = Cache::get(self::BLOCKED_IP_CACHE_KEY, []);

        if ($durationMinutes <= 0) {
            unset($blockedIps[$ip]);
        } else {
            $blockedIps[$ip] = now()->timestamp;
        }

        Cache::put(self::BLOCKED_IP_CACHE_KEY, $blockedIps, now()->addHours(24));

        Log::warning('IP address blocked', [
            'ip_address' => $ip,
            'reason'     => $reason ?? 'Manual block',
        ]);
    }

    public function isIpBlocked(string $ip): bool
    {
        return Cache::has(self::BLOCKED_IP_CACHE_KEY . ':' . $ip);
    }

    public function unblockIp(string $ip): void
    {
        Cache::forget(self::BLOCKED_IP_CACHE_KEY . ':' . $ip);
        Cache::forget(self::THREAT_COUNTER_CACHE_KEY . ':' . $ip);

        $blockedIps = Cache::get(self::BLOCKED_IP_CACHE_KEY, []);
        unset($blockedIps[$ip]);
        Cache::put(self::BLOCKED_IP_CACHE_KEY, $blockedIps, now()->addHours(24));
    }

    /**
     * @return array<string, mixed>
     */
    public function getSecurityMetrics(): array
    {
        $blockedIps = Cache::get(self::BLOCKED_IP_CACHE_KEY, []);

        $activeBlockedIps = array_values(array_filter(
            array_keys($blockedIps),
            fn (string $ip): bool => $this->isIpBlocked($ip)
        ));

        return [
            'threats_detected_today' => (int) Cache::get(self::THREATS_DETECTED_TODAY_KEY, 0),
            'blocked_ips_count'      => count($activeBlockedIps),
            'top_threat_types'       => Cache::get(self::THREAT_TYPE_COUNTER_CACHE_KEY, []),
            'suspicious_user_agents' => Cache::get(self::SUSPICIOUS_USER_AGENTS_KEY, []),
            'failed_login_attempts'  => (int) Cache::get(self::FAILED_LOGIN_ATTEMPTS_KEY, 0),
        ];
    }
}
