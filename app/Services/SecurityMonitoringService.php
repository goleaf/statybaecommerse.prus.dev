<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Security monitoring service for detecting and responding to security threats.
 */
class SecurityMonitoringService
{
    private const SUSPICIOUS_PATTERNS = [
        'sql_injection' => [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+.*set/i',
        ],
        'xss_attempts' => [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
        ],
        'path_traversal' => [
            '/\.\.\//i',
            '/\.\.\\\/i',
            '/etc\/passwd/i',
            '/proc\/self\/environ/i',
        ],
        'command_injection' => [
            '/;\s*cat\s+/i',
            '/;\s*ls\s+/i',
            '/;\s*rm\s+/i',
            '/`.*`/i',
            '/\$\(.*\)/i',
        ],
    ];

    public function __construct() {}

    /**
     * Monitor request for security threats.
     */
    public function monitorRequest(Request $request): array
    {
        $threats = [];
        $requestData = $this->extractRequestData($request);

        foreach (self::SUSPICIOUS_PATTERNS as $threatType => $patterns) {
            if ($this->detectThreat($requestData, $patterns)) {
                $threats[] = $threatType;
                $this->logSecurityThreat($request, $threatType, $requestData);
            }
        }

        if (!empty($threats)) {
            $this->trackSuspiciousActivity($request, $threats);
        }

        return $threats;
    }

    /**
     * Check if IP address should be blocked.
     */
    public function shouldBlockIp(string $ipAddress): bool
    {
        $key = "security:blocked_ip:{$ipAddress}";
        
        return Cache::has($key);
    }

    /**
     * Block IP address for specified duration.
     */
    public function blockIp(string $ipAddress, int $minutes = 60, string $reason = 'Security violation'): void
    {
        $key = "security:blocked_ip:{$ipAddress}";
        
        Cache::put($key, [
            'blocked_at' => now()->toISOString(),
            'reason' => $reason,
            'expires_at' => now()->addMinutes($minutes)->toISOString(),
        ], $minutes * 60);

        try {
            Log::channel('security')->warning('IP address blocked', [
                'ip_address' => $ipAddress,
                'reason' => $reason,
                'duration_minutes' => $minutes,
            ]);
        } catch (\Throwable) {
            Log::warning('IP address blocked', [
                'ip_address' => $ipAddress,
                'reason' => $reason,
            ]);
        }
    }

    /**
     * Get security metrics for monitoring dashboard.
     */
    public function getSecurityMetrics(): array
    {
        $cacheKey = 'security:metrics:' . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 3600, function () {
            return [
                'threats_detected_today' => $this->getThreatCount('today'),
                'blocked_ips_count' => $this->getBlockedIpsCount(),
                'top_threat_types' => $this->getTopThreatTypes(),
                'suspicious_user_agents' => $this->getSuspiciousUserAgents(),
                'failed_login_attempts' => $this->getFailedLoginAttempts(),
            ];
        });
    }

    /**
     * Extract request data for threat analysis.
     */
    private function extractRequestData(Request $request): array
    {
        return [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'query' => $request->query->all(),
            'input' => $request->input(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ];
    }

    /**
     * Detect threats in request data.
     */
    private function detectThreat(array $requestData, array $patterns): bool
    {
        $searchableData = json_encode($requestData);
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $searchableData)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log security threat with context.
     */
    private function logSecurityThreat(Request $request, string $threatType, array $requestData): void
    {
        // Use default log channel if security channel doesn't exist
        try {
            Log::channel('security')->warning('Security threat detected', [
            'threat_type' => $threatType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'request_id' => $request->header('X-Request-ID'),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
            'request_data' => $this->sanitizeRequestData($requestData),
        ]);
        } catch (\Throwable) {
            // Fallback to default log channel
            Log::warning('Security threat detected', [
                'threat_type' => $threatType,
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
        }
    }

    /**
     * Track suspicious activity for rate limiting and blocking.
     */
    private function trackSuspiciousActivity(Request $request, array $threats): void
    {
        $ipAddress = $request->ip();
        $key = "security:suspicious_activity:{$ipAddress}";
        
        $activity = Cache::get($key, []);
        $activity[] = [
            'timestamp' => now()->toISOString(),
            'threats' => $threats,
            'url' => $request->fullUrl(),
        ];

        // Keep only last 10 activities
        $activity = array_slice($activity, -10);
        
        Cache::put($key, $activity, 3600); // 1 hour

        // Auto-block if too many threats
        if (count($activity) >= 5) {
            $this->blockIp($ipAddress, 120, 'Multiple security violations');
        }
    }

    /**
     * Sanitize request data for logging (remove sensitive information).
     */
    private function sanitizeRequestData(array $requestData): array
    {
        $sensitiveKeys = ['password', 'token', 'api_key', 'secret', 'authorization'];
        
        return $this->recursiveSanitize($requestData, $sensitiveKeys);
    }

    /**
     * Recursively sanitize array data.
     */
    private function recursiveSanitize(array $data, array $sensitiveKeys): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->recursiveSanitize($value, $sensitiveKeys);
            } elseif (is_string($key) && $this->isSensitiveKey($key, $sensitiveKeys)) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Check if key is sensitive.
     */
    private function isSensitiveKey(string $key, array $sensitiveKeys): bool
    {
        $key = strtolower($key);
        
        foreach ($sensitiveKeys as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get threat count for specified period.
     */
    private function getThreatCount(string $period): int
    {
        // This would typically query a database or log aggregation service
        // For now, return a placeholder
        return 0;
    }

    /**
     * Get count of currently blocked IPs.
     */
    private function getBlockedIpsCount(): int
    {
        // This would scan cache for blocked IP keys
        return 0;
    }

    /**
     * Get top threat types.
     */
    private function getTopThreatTypes(): array
    {
        // This would aggregate threat data
        return [];
    }

    /**
     * Get suspicious user agents.
     */
    private function getSuspiciousUserAgents(): array
    {
        // This would analyze user agent patterns
        return [];
    }

    /**
     * Get failed login attempts count.
     */
    private function getFailedLoginAttempts(): int
    {
        // This would query authentication logs
        return 0;
    }
}